@props(['heading', 'message'])

<section class="rounded-card px-4 py-10 text-center sm:px-8 sm:py-14" aria-labelledby="empty-state-heading">
    <div class="mx-auto flex size-[clamp(7.5rem,20vw,16.5rem)] items-center justify-center rounded-full border-[5px] border-brand-danger text-[clamp(5rem,12vw,12rem)] font-light leading-none text-brand-danger" aria-hidden="true">×</div>
    <h2 id="empty-state-heading" class="mt-6 text-[clamp(1.75rem,3.4vw,3rem)] font-bold">{{ $heading }}</h2>
    <p class="mx-auto mt-4 max-w-[570px] text-lg leading-relaxed text-brand-ink/80 sm:text-xl">{{ $message }}</p>
    @isset($actions)
        <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row">
            {{ $actions }}
        </div>
    @endisset
</section>
