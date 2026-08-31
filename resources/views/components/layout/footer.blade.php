<footer class="px-[clamp(18px,3vw,32px)] pt-[clamp(30px,3.6vw,56px)] pb-[clamp(34px,4vw,56px)] text-center">
    <nav aria-label="Institucional" class="mb-5 flex flex-wrap items-center justify-center gap-x-4 gap-y-1.5 font-display text-[12.5px] font-semibold md:hidden">
        @if (\App\Models\SectionTypeSetting::isEnabled(\App\Enums\SectionType::About))
            <a href="{{ route('about') }}" title="Sobre" class="text-[#9BA3B2] hover:text-brand-purple">Sobre</a>
        @endif
        @if (\App\Enums\SiteModule::Blog->isEnabled())
            <a href="{{ route('blog.index') }}" title="Blog" class="text-[#9BA3B2] hover:text-brand-purple">Blog</a>
        @endif
        @if (\App\Models\SectionTypeSetting::isEnabled(\App\Enums\SectionType::FaqGroup))
            <a href="{{ route('faq.index') }}" title="Perguntas frequentes" class="text-[#9BA3B2] hover:text-brand-purple">Dúvidas</a>
        @endif
        <a href="{{ route('contact.show') }}" title="Contato" class="text-[#9BA3B2] hover:text-brand-purple">Contato</a>
    </nav>

    <img src="{{ $company?->logoUrl() ?? asset('imgs/logo.png') }}" alt="{{ \App\Models\Company::siteName() }}" class="mx-auto h-[clamp(44px,5vw,56px)] w-auto opacity-90">
    <p class="font-display mt-2.5 text-[clamp(13px,1.3vw,15px)] font-semibold text-brand-purple">#CorpoDeVerdade · #LooksDeVerdade</p>
    <p class="mt-3 text-[clamp(11.5px,1.2vw,13px)] leading-[1.6] text-[#9BA3B2]">{{ \App\Models\Company::siteName() }} · Moda fitness para todos os corpos<br>Pedidos e trocas pelo WhatsApp · @fitbycae</p>
</footer>
