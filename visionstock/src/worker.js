import { Container, getRandom } from "@cloudflare/containers";

const REQUIRED_ENVIRONMENT = ["APP_KEY"];
const CONTAINER_INSTANCE_COUNT = 3;

export class VisionStockContainer extends Container {
    defaultPort = 80;
    sleepAfter = "10m";
    pingEndpoint = "localhost/up";
    enableInternet = true;

    constructor(ctx, env) {
        super(ctx, env);

        const databaseUrl = env.DB_URL || env.POSTGRES_URL || env.MYSQL_URL;

        this.envVars = {
            APP_NAME: env.APP_NAME || "VisionStock",
            APP_ENV: "production",
            APP_KEY: env.APP_KEY || "",
            APP_DEBUG: "false",
            APP_URL: env.APP_URL || "",
            LOG_CHANNEL: "stderr",
            LOG_LEVEL: env.LOG_LEVEL || "warning",
            DB_CONNECTION: env.DB_CONNECTION || "pgsql",
            DB_URL: databaseUrl || "",
            DB_HOST: env.DB_HOST || "",
            DB_PORT: env.DB_PORT || "",
            DB_DATABASE: env.DB_DATABASE || "",
            DB_USERNAME: env.DB_USERNAME || "",
            DB_PASSWORD: env.DB_PASSWORD || "",
            DB_SSLMODE: env.DB_SSLMODE || "require",
            SESSION_DRIVER: "cookie",
            SESSION_SECURE_COOKIE: "true",
            CACHE_STORE: "array",
            QUEUE_CONNECTION: "sync",
            FILESYSTEM_DISK: env.FILESYSTEM_DISK || "s3",
            AWS_ACCESS_KEY_ID: env.AWS_ACCESS_KEY_ID || "",
            AWS_SECRET_ACCESS_KEY: env.AWS_SECRET_ACCESS_KEY || "",
            AWS_DEFAULT_REGION: env.AWS_DEFAULT_REGION || "auto",
            AWS_BUCKET: env.AWS_BUCKET || "",
            AWS_URL: env.AWS_URL || "",
            AWS_ENDPOINT: env.AWS_ENDPOINT || "",
            AWS_USE_PATH_STYLE_ENDPOINT: env.AWS_USE_PATH_STYLE_ENDPOINT || "true",
        };
    }
}

export default {
    async fetch(request, env) {
        const missingVariables = REQUIRED_ENVIRONMENT.filter(
            (name) => !env[name],
        );

        if (!env.DB_URL && !env.POSTGRES_URL && !env.MYSQL_URL && !env.DB_HOST) {
            missingVariables.push("POSTGRES_URL (or MYSQL_URL / DB_URL / DB_HOST)");
        }

        if (missingVariables.length > 0) {
            return Response.json(
                {
                    error: "Cloudflare Worker configuration is incomplete.",
                    missing: missingVariables,
                },
                { status: 503 },
            );
        }

        try {
            const container = await getRandom(
                env.VISIONSTOCK,
                CONTAINER_INSTANCE_COUNT,
            );

            return await container.fetch(request);
        } catch (error) {
            console.error("VisionStock container request failed.", error);

            return Response.json(
                {
                    error: "VisionStock container is unavailable. Check Cloudflare Worker and Container logs.",
                },
                { status: 502 },
            );
        }
    },
};
