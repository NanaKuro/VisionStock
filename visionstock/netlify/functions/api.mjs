import { getStore } from "@netlify/blobs";
import bcrypt from "bcryptjs";
import { createHmac, randomBytes, randomUUID, timingSafeEqual } from "node:crypto";
import postgres from "postgres";

const CATEGORY_NAMES = [
    "Alat Tulis",
    "Bahan Baku Makanan",
    "Buku",
    "Minuman",
    "Makanan",
    "Perlengkapan Rumah Tangga",
    "Barang lainnya",
];
const SESSION_COOKIE = "visionstock_session";
const SESSION_TTL_SECONDS = 60 * 60 * 24 * 14;
const MAX_UPLOAD_BYTES = 3.5 * 1024 * 1024;
const MIME_TYPES = new Map([
    [".jpg", "image/jpeg"],
    [".jpeg", "image/jpeg"],
    [".png", "image/png"],
    [".pdf", "application/pdf"],
]);
let database;

function getDatabase() {
    const connectionString = process.env.NETLIFY_DB_URL;
    if (!connectionString) {
        throw new HttpError(503, "Netlify Database belum dihubungkan ke site ini.");
    }

    database ??= postgres(connectionString, {
        max: 1,
        idle_timeout: 20,
        connect_timeout: 10,
        ssl: "require",
    });

    return database;
}

class HttpError extends Error {
    constructor(status, message) {
        super(message);
        this.status = status;
    }
}

function json(data, status = 200, headers = {}) {
    return Response.json(data, {
        status,
        headers: {
            "Cache-Control": "no-store",
            "X-Content-Type-Options": "nosniff",
            ...headers,
        },
    });
}

function getCookie(request, name) {
    const cookies = request.headers.get("cookie") ?? "";
    const prefix = `${name}=`;
    const item = cookies.split(";").map((part) => part.trim()).find((part) => part.startsWith(prefix));
    if (!item) return null;
    try {
        return decodeURIComponent(item.slice(prefix.length));
    } catch {
        return null;
    }
}

function sessionSecret() {
    const secret = process.env.APP_SESSION_SECRET;
    if (!secret || secret.length < 32) {
        throw new HttpError(503, "APP_SESSION_SECRET harus diatur dengan nilai acak minimal 32 karakter.");
    }
    return secret;
}

function sign(payload) {
    return createHmac("sha256", sessionSecret()).update(payload).digest("base64url");
}

function makeSession(userId) {
    const payload = Buffer.from(JSON.stringify({
        sub: String(userId),
        exp: Math.floor(Date.now() / 1000) + SESSION_TTL_SECONDS,
        nonce: randomBytes(16).toString("base64url"),
    })).toString("base64url");

    return `${payload}.${sign(payload)}`;
}

function readSession(request) {
    const token = getCookie(request, SESSION_COOKIE);
    if (!token) return null;

    const [payload, signature, extra] = token.split(".");
    if (!payload || !signature || extra) return null;

    const expected = Buffer.from(sign(payload));
    const actual = Buffer.from(signature);
    if (expected.length !== actual.length || !timingSafeEqual(expected, actual)) return null;

    try {
        const data = JSON.parse(Buffer.from(payload, "base64url").toString("utf8"));
        return typeof data.sub === "string" && data.exp > Date.now() / 1000 ? data.sub : null;
    } catch {
        return null;
    }
}

function cookieHeader(request, token) {
    const secure = new URL(request.url).protocol === "https:" ? "; Secure" : "";
    const value = token ? encodeURIComponent(token) : "";
    const maxAge = token ? SESSION_TTL_SECONDS : 0;
    return `${SESSION_COOKIE}=${value}; Path=/; HttpOnly; SameSite=Lax; Max-Age=${maxAge}${secure}`;
}

async function bodyJson(request) {
    if (!request.headers.get("content-type")?.includes("application/json")) {
        throw new HttpError(415, "Permintaan harus menggunakan format JSON.");
    }
    try {
        return await request.json();
    } catch {
        throw new HttpError(400, "Isi permintaan tidak valid.");
    }
}

function requiredText(value, label, maxLength = 255) {
    if (typeof value !== "string" || !value.trim() || value.trim().length > maxLength) {
        throw new HttpError(422, `${label} wajib diisi dan maksimal ${maxLength} karakter.`);
    }
    return value.trim();
}

function nonnegativeInteger(value, label) {
    const number = Number(value);
    if (!Number.isSafeInteger(number) || number < 0) {
        throw new HttpError(422, `${label} harus berupa bilangan bulat nol atau lebih.`);
    }
    return number;
}

function validateDate(value) {
    if (typeof value !== "string" || !/^\d{4}-\d{2}-\d{2}(?:T.*)?$/.test(value) || Number.isNaN(Date.parse(value))) {
        throw new HttpError(422, "Tanggal tidak valid.");
    }
    const [year, month, day] = value.slice(0, 10).split("-").map(Number);
    const parsedDate = new Date(Date.UTC(year, month - 1, day));
    if (parsedDate.getUTCFullYear() !== year || parsedDate.getUTCMonth() !== month - 1 || parsedDate.getUTCDate() !== day) {
        throw new HttpError(422, "Tanggal tidak valid.");
    }
    return new Date(value);
}

function isValidDateOnly(value) {
    if (typeof value !== "string" || !/^\d{4}-\d{2}-\d{2}$/.test(value)) return false;
    const [year, month, day] = value.split("-").map(Number);
    const parsedDate = new Date(Date.UTC(year, month - 1, day));
    return parsedDate.getUTCFullYear() === year && parsedDate.getUTCMonth() === month - 1 && parsedDate.getUTCDate() === day;
}

async function currentUser(request, db) {
    const id = readSession(request);
    if (!id) throw new HttpError(401, "Silakan masuk terlebih dahulu.");

    const [user] = await db`
        SELECT id, name, email, username, login_identifier
        FROM users
        WHERE id = ${id}
    `;
    if (!user) throw new HttpError(401, "Sesi tidak valid. Silakan masuk kembali.");
    return user;
}

async function saveUpload(request, user, db) {
    const form = await request.formData();
    const file = form.get("file");
    const kind = form.get("kind");

    if (!(file instanceof File) || !["image", "invoice"].includes(kind)) {
        throw new HttpError(422, "Berkas unggahan tidak valid.");
    }
    if (!file.size || file.size > MAX_UPLOAD_BYTES) {
        throw new HttpError(413, "Ukuran berkas maksimal 3,5 MB.");
    }
    const allowed = kind === "image"
        ? new Set(["image/jpeg", "image/png"])
        : new Set(["application/pdf", "image/jpeg", "image/png"]);
    if (!allowed.has(file.type)) {
        throw new HttpError(422, kind === "image"
            ? "Gambar harus berformat JPG atau PNG."
            : "Invoice harus berformat PDF, JPG, atau PNG.");
    }

    const originalName = file.name.replace(/[^a-zA-Z0-9._-]/g, "_").slice(-100) || "upload";
    const key = `users/${user.id}/${kind}/${randomUUID()}-${originalName}`;
    const store = getStore("visionstock-uploads");
    await store.set(key, file, {
        metadata: { contentType: file.type },
    });

    return key;
}

async function listProducts(request, user, db) {
    const url = new URL(request.url);
    const search = url.searchParams.get("search")?.trim() ?? "";
    const categoryName = url.searchParams.get("category_name") ?? "";
    const sorts = {
        name_asc: "p.name ASC",
        name_desc: "p.name DESC",
        stock_desc: "p.stock DESC, p.name ASC",
        stock_asc: "p.stock ASC, p.name ASC",
    };
    const sort = sorts[url.searchParams.get("sort")] ?? sorts.name_asc;
    const rows = await db.unsafe(
        `SELECT p.id, p.name, p.description, p.stock, p.image_path, p.category_id,
                c.name AS category_name, p.created_at, p.updated_at
         FROM products p
         LEFT JOIN product_categories c ON c.id = p.category_id AND c.user_id = p.user_id
         WHERE p.user_id = $1
           AND ($2 = '' OR p.name ILIKE '%' || $2 || '%')
           AND ($3 = '' OR c.name = $3)
         ORDER BY ${sort}
         LIMIT 500`,
        [user.id, search, categoryName],
    );
    return rows;
}

async function createProduct(user, data, db) {
    const name = requiredText(data.name, "Nama produk");
    const stock = nonnegativeInteger(data.stock, "Stok");
    const categoryName = typeof data.category_name === "string" ? data.category_name.trim() : "";
    if (categoryName.length > 255) throw new HttpError(422, "Jenis produk maksimal 255 karakter.");
    let categoryId = null;
    if (categoryName) {
        const [category] = await db`
            INSERT INTO product_categories (user_id, name)
            VALUES (${user.id}, ${categoryName})
            ON CONFLICT (user_id, name) DO UPDATE SET name = EXCLUDED.name
            RETURNING id
        `;
        categoryId = category.id;
    }
    const description = typeof data.description === "string" ? data.description.trim().slice(0, 5000) : null;
    const imagePath = typeof data.image_path === "string" && data.image_path.startsWith(`users/${user.id}/image/`)
        ? data.image_path
        : null;

    const [product] = await db`
        INSERT INTO products (user_id, category_id, name, description, stock, image_path)
        VALUES (${user.id}, ${categoryId}, ${name}, ${description}, ${stock}, ${imagePath})
        RETURNING id, name, description, stock, image_path, category_id, created_at, updated_at
    `;
    return { ...product, category_name: categoryName };
}

async function importProducts(user, data, db) {
    if (!Array.isArray(data.rows) || data.rows.length > 500) {
        throw new HttpError(422, "Data impor harus berupa daftar maksimal 500 baris.");
    }
    let importedCount = 0;
    const failures = [];

    for (const [index, row] of data.rows.entries()) {
        const rowNumber = index + 2;
        const name = String(row?.nama_produk ?? "").trim();
        const categoryName = String(row?.jenis_produk ?? "").trim();
        const stockValue = row?.stok;
        const stock = Number(stockValue);
        const description = typeof row?.deskripsi === "string" ? row.deskripsi.trim() : null;
        const messages = [];
        if (!name || name.length > 255) messages.push("Nama produk wajib diisi dan maksimal 255 karakter.");
        if (categoryName.length > 255) messages.push("Jenis produk maksimal 255 karakter.");
        if (stockValue === "" || stockValue === null || stockValue === undefined || !Number.isSafeInteger(stock) || stock < 0) {
            messages.push("Stok harus berupa bilangan bulat nol atau lebih.");
        }

        if (messages.length) {
            failures.push({ row: rowNumber, messages });
            continue;
        }

        try {
            await createProduct(user, { name, category_name: categoryName, stock, description }, db);
            importedCount++;
        } catch (error) {
            if (error.code === "23505") messages.push("Nama produk tersebut sudah digunakan.");
            else throw error;
            failures.push({ row: rowNumber, messages });
        }
    }
    return { imported_count: importedCount, failures };
}

async function importTransactions(user, data, db) {
    if (!Array.isArray(data.rows) || data.rows.length > 500) {
        throw new HttpError(422, "Data impor harus berupa daftar maksimal 500 baris.");
    }
    let importedCount = 0;
    const failures = [];

    for (const [index, row] of data.rows.entries()) {
        const rowNumber = index + 2;
        const name = String(row?.nama_produk ?? "").trim();
        const quantity = Number(row?.stok_terjual);
        const soldAt = typeof row?.tanggal_terjual === "string" ? row.tanggal_terjual.trim() : "";
        const messages = [];
        if (!name) messages.push("Nama produk wajib diisi.");
        if (!Number.isSafeInteger(quantity) || quantity < 1) messages.push("Stok terjual harus berupa bilangan bulat minimal 1.");
        if (!isValidDateOnly(soldAt)) {
            messages.push("Tanggal terjual harus berformat YYYY-MM-DD.");
        }
        if (messages.length) {
            failures.push({ row: rowNumber, messages });
            continue;
        }

        const result = await db.begin(async (tx) => {
            const [product] = await tx`
                SELECT id, stock FROM products
                WHERE user_id = ${user.id} AND name = ${name}
                FOR UPDATE
            `;
            if (!product) return { error: "Produk tidak ditemukan pada data stok akun ini." };
            if (quantity > product.stock) {
                return { error: `Stok terjual melebihi stok tersedia (${product.stock}). Transaksi tidak disimpan.` };
            }
            await tx`
                UPDATE products SET stock = stock - ${quantity}, updated_at = NOW()
                WHERE id = ${product.id} AND user_id = ${user.id}
            `;
            await tx`
                INSERT INTO transactions (user_id, product_id, quantity_sold, sold_at)
                VALUES (${user.id}, ${product.id}, ${quantity}, ${`${soldAt}T00:00:00Z`})
            `;
            return { ok: true };
        });

        if (result.error) failures.push({ row: rowNumber, messages: [result.error] });
        else importedCount++;
    }
    return { imported_count: importedCount, failures };
}

async function analysisForUser(user, db) {
    return db`
        SELECT p.id, p.name, p.stock,
               COALESCE(SUM(t.quantity_sold), 0)::integer AS units_sold_in_period,
               GREATEST(COALESCE(SUM(t.quantity_sold), 0)::integer - p.stock, 0) AS recommended_stock
        FROM products p
        LEFT JOIN transactions t
          ON t.product_id = p.id AND t.user_id = p.user_id
         AND t.sold_at >= NOW() - INTERVAL '30 days'
        WHERE p.user_id = ${user.id}
        GROUP BY p.id
        ORDER BY p.name
    `;
}

async function handle(request) {
    const db = getDatabase();
    const url = new URL(request.url);
    const path = url.pathname.replace(/\/+$/, "") || "/";
    const method = request.method.toUpperCase();

    if (path === "/api/auth/register" && method === "POST") {
        const data = await bodyJson(request);
        const identifier = requiredText(data.identifier, "Username atau email").toLowerCase();
        if (identifier.length < 3 || !/^[a-z0-9._@+-]+$/.test(identifier)) {
            throw new HttpError(422, "Username atau email tidak valid.");
        }
        if (typeof data.password !== "string" || data.password.length < 8 || data.password.length > 128) {
            throw new HttpError(422, "Password harus berisi 8 sampai 128 karakter.");
        }
        const email = identifier.includes("@") ? identifier : null;
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            throw new HttpError(422, "Format email tidak valid.");
        }
        const username = email ? null : identifier;
        sessionSecret();
        const password = await bcrypt.hash(data.password, 12);
        const user = await db.begin(async (tx) => {
            const [created] = await tx`
                INSERT INTO users (name, email, username, login_identifier, password)
                VALUES (${identifier}, ${email}, ${username}, ${identifier}, ${password})
                RETURNING id, name, email, username, login_identifier
            `;
            for (const categoryName of CATEGORY_NAMES) {
                await tx`
                    INSERT INTO product_categories (user_id, name)
                    VALUES (${created.id}, ${categoryName})
                    ON CONFLICT (user_id, name) DO NOTHING
                `;
            }
            return created;
        });
        return json({ user }, 201, { "Set-Cookie": cookieHeader(request, makeSession(user.id)) });
    }

    if (path === "/api/auth/login" && method === "POST") {
        const data = await bodyJson(request);
        const identifier = requiredText(data.identifier, "Username atau email").toLowerCase();
        const [user] = await db`
            SELECT id, name, email, username, login_identifier, password
            FROM users WHERE login_identifier = ${identifier}
        `;
        if (!user || typeof data.password !== "string" || !(await bcrypt.compare(data.password, user.password))) {
            throw new HttpError(401, "Username/email atau password yang dimasukkan salah.");
        }
        delete user.password;
        return json({ user }, 200, { "Set-Cookie": cookieHeader(request, makeSession(user.id)) });
    }

    if (path === "/api/auth/logout" && method === "POST") {
        return json({ ok: true }, 200, { "Set-Cookie": cookieHeader(request, null) });
    }

    if (path === "/api/auth/me" && method === "GET") {
        return json({ user: await currentUser(request, db) });
    }

    const user = await currentUser(request, db);

    if (path === "/api/categories" && method === "GET") {
        const categories = await db`
            SELECT name FROM product_categories WHERE user_id = ${user.id} ORDER BY name
        `;
        return json({ categories: categories.map((category) => category.name) });
    }

    if (path === "/api/dashboard" && method === "GET") {
        const [metrics] = await db`
            SELECT
                (SELECT COUNT(*)::integer FROM transactions WHERE user_id = ${user.id}) AS transaction_records,
                (SELECT COUNT(*)::integer FROM products WHERE user_id = ${user.id}) AS products,
                (SELECT COALESCE(SUM(stock), 0)::integer FROM products WHERE user_id = ${user.id}) AS units_in_stock,
                (SELECT COALESCE(SUM(quantity_sold), 0)::integer FROM transactions WHERE user_id = ${user.id}) AS units_sold
        `;
        return json({ metrics });
    }

    if (path === "/api/products" && method === "GET") {
        return json({ products: await listProducts(request, user, db), categories: CATEGORY_NAMES });
    }

    if (path === "/api/products" && method === "POST") {
        const product = await createProduct(user, await bodyJson(request), db);
        return json({ product }, 201);
    }

    const productMatch = path.match(/^\/api\/products\/(\d+)$/);
    if (productMatch && method === "PATCH") {
        const data = await bodyJson(request);
        const id = productMatch[1];
        const name = requiredText(data.name, "Nama produk");
        const stock = nonnegativeInteger(data.stock, "Stok");
        const categoryName = typeof data.category_name === "string" ? data.category_name.trim() : "";
        if (categoryName.length > 255) throw new HttpError(422, "Jenis produk maksimal 255 karakter.");
        let categoryId = null;
        if (categoryName) {
            const [category] = await db`
                INSERT INTO product_categories (user_id, name)
                VALUES (${user.id}, ${categoryName})
                ON CONFLICT (user_id, name) DO UPDATE SET name = EXCLUDED.name
                RETURNING id
            `;
            categoryId = category.id;
        }
        const imagePath = typeof data.image_path === "string" && data.image_path.startsWith(`users/${user.id}/image/`)
            ? data.image_path
            : null;
        const [product] = await db`
            UPDATE products SET name = ${name}, stock = ${stock}, category_id = ${categoryId},
                description = ${typeof data.description === "string" ? data.description.trim().slice(0, 5000) : null},
                image_path = COALESCE(${imagePath}, image_path),
                updated_at = NOW()
            WHERE id = ${id} AND user_id = ${user.id}
            RETURNING id, name, description, stock, image_path, category_id, updated_at
        `;
        if (!product) throw new HttpError(404, "Produk tidak ditemukan.");
        return json({ product: { ...product, category_name: categoryName } });
    }

    if (path === "/api/transactions" && method === "GET") {
        const search = url.searchParams.get("search")?.trim() ?? "";
        const sorts = {
            date_desc: "t.sold_at DESC",
            date_asc: "t.sold_at ASC",
            quantity_desc: "t.quantity_sold DESC",
            quantity_asc: "t.quantity_sold ASC",
        };
        const sort = sorts[url.searchParams.get("sort")] ?? sorts.date_desc;
        const transactions = await db.unsafe(
            `SELECT t.id, t.quantity_sold, t.sold_at, t.invoice_path, p.name AS product_name
             FROM transactions t JOIN products p ON p.id = t.product_id AND p.user_id = t.user_id
             WHERE t.user_id = $1 AND ($2 = '' OR p.name ILIKE '%' || $2 || '%')
             ORDER BY ${sort} LIMIT 500`,
            [user.id, search],
        );
        return json({ transactions });
    }

    if (path === "/api/transactions" && method === "POST") {
        const data = await bodyJson(request);
        const productId = String(data.product_id ?? "");
        const quantity = Number(data.quantity_sold);
        if (!/^\d+$/.test(productId) || !Number.isSafeInteger(quantity) || quantity < 1) {
            throw new HttpError(422, "Produk dan jumlah transaksi harus valid.");
        }
        const soldAt = validateDate(data.sold_at);
        const transaction = await db.begin(async (tx) => {
            const [product] = await tx`
                SELECT id, stock FROM products WHERE id = ${productId} AND user_id = ${user.id} FOR UPDATE
            `;
            if (!product) throw new HttpError(404, "Produk tidak ditemukan.");
            if (quantity > product.stock) throw new HttpError(422, `Stok terjual melebihi stok tersedia (${product.stock}).`);
            const invoicePath = typeof data.invoice_path === "string"
                && data.invoice_path.startsWith(`users/${user.id}/invoice/`)
                ? data.invoice_path
                : null;
            await tx`UPDATE products SET stock = stock - ${quantity}, updated_at = NOW() WHERE id = ${productId}`;
            const [created] = await tx`
                INSERT INTO transactions (user_id, product_id, quantity_sold, sold_at, invoice_path)
                VALUES (${user.id}, ${productId}, ${quantity}, ${soldAt}, ${invoicePath})
                RETURNING id, quantity_sold, sold_at, invoice_path
            `;
            return created;
        });
        return json({ transaction }, 201);
    }

    if (path === "/api/import/products" && method === "POST") {
        return json(await importProducts(user, await bodyJson(request), db));
    }

    if (path === "/api/import/transactions" && method === "POST") {
        return json(await importTransactions(user, await bodyJson(request), db));
    }

    if (path === "/api/analysis" && method === "GET") {
        return json({ products: await analysisForUser(user, db), period_days: 30 });
    }

    if (path === "/api/analysis/history" && method === "GET") {
        const histories = await db`
            SELECT h.id, h.analyzed_at, h.period_days, h.coverage_days, COUNT(i.id)::integer AS item_count
            FROM analysis_histories h
            LEFT JOIN analysis_history_items i ON i.analysis_history_id = h.id
            WHERE h.user_id = ${user.id}
            GROUP BY h.id
            ORDER BY h.analyzed_at DESC, h.id DESC
            LIMIT 200
        `;
        return json({ histories });
    }

    if (path === "/api/analysis/history" && method === "POST") {
        const products = await analysisForUser(user, db);
        if (!products.length) throw new HttpError(422, "Belum ada data stok untuk disimpan sebagai riwayat analisis.");
        const history = await db.begin(async (tx) => {
            const [created] = await tx`
                INSERT INTO analysis_histories (user_id, analyzed_at, period_days, coverage_days)
                VALUES (${user.id}, NOW(), 30, 30)
                RETURNING id, analyzed_at, period_days, coverage_days
            `;
            for (const product of products) {
                await tx`
                    INSERT INTO analysis_history_items
                        (analysis_history_id, product_id, product_name, stock, recommended_stock)
                    VALUES (${created.id}, ${product.id}, ${product.name}, ${product.stock}, ${product.recommended_stock})
                `;
            }
            return created;
        });
        return json({ history }, 201);
    }

    const historyMatch = path.match(/^\/api\/analysis\/history\/(\d+)$/);
    if (historyMatch && method === "GET") {
        const [history] = await db`
            SELECT id, analyzed_at, period_days, coverage_days
            FROM analysis_histories WHERE id = ${historyMatch[1]} AND user_id = ${user.id}
        `;
        if (!history) throw new HttpError(404, "Riwayat analisis tidak ditemukan.");
        history.items = await db`
            SELECT product_id, product_name, stock, recommended_stock
            FROM analysis_history_items
            WHERE analysis_history_id = ${history.id}
            ORDER BY product_name
        `;
        return json({ history });
    }

    if (path === "/api/profile" && method === "GET") return json({ user });

    if (path === "/api/profile" && method === "PATCH") {
        const data = await bodyJson(request);
        const name = requiredText(data.name, "Nama");
        const email = typeof data.email === "string" && data.email.trim() ? data.email.trim().toLowerCase() : null;
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) throw new HttpError(422, "Format email tidak valid.");
        let password = null;
        if (data.password) {
            if (typeof data.password !== "string" || data.password.length < 8 || data.password.length > 128) {
                throw new HttpError(422, "Password harus berisi 8 sampai 128 karakter.");
            }
            password = await bcrypt.hash(data.password, 12);
        }
        const loginIdentifier = user.email && user.login_identifier === user.email && email
            ? email
            : user.login_identifier;
        const [updated] = await db`
            UPDATE users SET name = ${name}, email = ${email},
                login_identifier = ${loginIdentifier},
                password = COALESCE(${password}, password), updated_at = NOW()
            WHERE id = ${user.id}
            RETURNING id, name, email, username, login_identifier
        `;
        return json({ user: updated });
    }

    if (path === "/api/uploads" && method === "POST") {
        const key = await saveUpload(request, user, db);
        return json({ key, url: `/api/uploads/${encodeURIComponent(key)}` }, 201);
    }

    const uploadMatch = path.match(/^\/api\/uploads\/(.+)$/);
    if (uploadMatch && method === "GET") {
        const key = decodeURIComponent(uploadMatch[1]);
        if (!key.startsWith(`users/${user.id}/`)) throw new HttpError(404, "Berkas tidak ditemukan.");
        const file = await getStore("visionstock-uploads").get(key, { type: "arrayBuffer" });
        if (!file) throw new HttpError(404, "Berkas tidak ditemukan.");
        const extension = key.slice(key.lastIndexOf(".")).toLowerCase();
        return new Response(file, {
            headers: {
                "Content-Type": MIME_TYPES.get(extension) ?? "application/octet-stream",
                "Cache-Control": "private, max-age=3600",
                "X-Content-Type-Options": "nosniff",
            },
        });
    }

    throw new HttpError(404, "Endpoint tidak ditemukan.");
}

export default async function handler(request) {
    try {
        return await handle(request);
    } catch (error) {
        if (error instanceof HttpError) return json({ error: error.message }, error.status);
        if (error?.code === "23505") return json({ error: "Data sudah digunakan oleh akun ini." }, 409);

        console.error("Netlify API request failed.", error);
        return json({ error: "Terjadi kesalahan pada server. Periksa log Netlify Functions." }, 500);
    }
}

export const config = {
    path: ["/api", "/api/*"],
};
