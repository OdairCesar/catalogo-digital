<?php

namespace Database\Seeders;

use App\Enums\SectionType;
use App\Models\Section;
use App\Models\SectionTypeSetting;
use Illuminate\Database\Seeder;

/**
 * Seeds the home page blocks, testimonials, and FAQ groups that were
 * previously hardcoded in Blade, so the site keeps its current copy the
 * first time this migration/seeder runs and Cae can then edit it in the
 * admin panel under "Seções".
 */
class SectionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (SectionType::togglables() as $type) {
            SectionTypeSetting::query()->firstOrCreate(['type' => $type], ['enabled' => true]);
        }

        Section::query()->updateOrCreate(['type' => SectionType::HomeHero], [
            'title' => 'Looks de verdade para corpos de verdade.',
            'content' => 'Sou a Cae. Eu escolho, visto e testo cada peça antes de chegar em você — pra você se sentir gata do jeito que você é.',
            'data' => [
                'badge' => 'Coleção Abraço · do 36 ao 52',
                'cta_label' => 'Ver a coleção',
            ],
        ]);

        Section::query()->updateOrCreate(['type' => SectionType::HomeTrustBar], [
            'data' => [
                'items' => [
                    ['title' => '36 ao 52', 'subtitle' => 'todos os corpos'],
                    ['title' => 'Troca fácil', 'subtitle' => 'sem estresse'],
                    ['title' => 'Envio 24h', 'subtitle' => 'a gente corre'],
                ],
            ],
        ]);

        Section::query()->updateOrCreate(['type' => SectionType::Instagram], [
            'title' => 'Clientes reais, sem filtro',
            'content' => 'Marca @fitbycae que eu reposto o seu look.',
        ]);

        Section::query()->updateOrCreate(['type' => SectionType::HomeWhatsappBanner], [
            'title' => 'Fala comigo, gata',
            'content' => 'Na dúvida do tamanho? Me manda sua medida e a foto do look que você quer. Eu digo com sinceridade o que veste bem em você — mesmo que a resposta seja "esse não, amiga".',
            'data' => [
                'subtitle' => 'Cae · respondo eu mesma',
                'button_label' => 'Chamar a Cae no WhatsApp',
            ],
        ]);

        Section::query()->updateOrCreate(['type' => SectionType::About], [
            'title' => 'A Fit By Cae não vende só peças de treino — vende autoestima',
            'data' => [
                'intro_paragraphs' => [
                    ['paragraph' => 'A proposta central é valorizar o corpo real de cada cliente, com looks que funcionam tanto na academia quanto no dia a dia. A gente se diferencia das marcas "fitness padrão" ao rejeitar o corpo idealizado e inatingível, celebrando corpos reais, com curvas, texturas e tamanhos diversos — do 36 ao 52.'],
                    ['paragraph' => 'Roupas fitness que vestem bem, valorizam e fazem você se sentir bonita e confiante — sem padronização de corpo ou photoshop irreal.'],
                ],
                'missao_text' => 'Ajudar mulheres a se sentirem fortes e bonitas no próprio corpo, através de moda fitness acessível e autêntica.',
                'quem_escolhe_text' => 'Sou a Cae. Eu escolho, visto e testo cada peça antes de chegar em você — nada entra no catálogo sem passar por mim primeiro.',
                'values' => [
                    ['title' => 'Autenticidade', 'desc' => 'Preferimos peças reais, testadas de verdade, a promessas de corpo perfeito.'],
                    ['title' => 'Representatividade', 'desc' => 'Corpos com curvas, texturas e tamanhos diversos — do 36 ao 52 em todas as peças.'],
                    ['title' => 'Autoestima', 'desc' => 'A gente vende autoestima tanto quanto vende roupa. Uma coisa não vem sem a outra.'],
                    ['title' => 'Proximidade', 'desc' => 'Conversa de amiga, não de vitrine. Você fala direto com a Cae, sem letra miúda.'],
                ],
                'manifesto_paragraphs' => [
                    ['paragraph' => 'Na Fit By Cae, a gente acredita que corpo real é bonito do jeito que é.'],
                    ['paragraph' => 'Sem corpo padrão, sem photoshop, sem cobrança — só looks que vestem bem em quem você é hoje.'],
                    ['paragraph' => 'Autoestima também é peça de roupa. A gente escolhe, veste e testa cada uma antes de chegar em você.'],
                ],
                'manifesto_tagline' => 'Looks de verdade para corpos de verdade.',
                'cta_title' => 'Ficou com vontade de conhecer os looks?',
                'cta_description' => 'Dá uma olhada na coleção ou fala direto comigo — sem compromisso.',
                'cta_button_label' => 'Falar com a Cae',
            ],
        ]);

        $testimonials = [
            ['content' => 'Achei que legging roxa não era pra mim. Usei no treino, fui no mercado e recebi 3 elogios. Tô convertida.', 'data' => ['author_name' => 'Marina, 27', 'author_detail' => 'veste 44']],
            ['content' => 'A Cae respondeu meu zap num domingo e me ajudou a escolher o tamanho. Vestiu perfeito de primeira.', 'data' => ['author_name' => 'Rafa, 34', 'author_detail' => 'veste 48']],
            ['content' => 'Primeira vez que me senti bonita numa foto de treino. Sério.', 'data' => ['author_name' => 'Bia, 22', 'author_detail' => 'veste 38']],
        ];

        foreach ($testimonials as $sortOrder => $testimonial) {
            Section::query()->updateOrCreate(
                ['type' => SectionType::Testimonial, 'sort_order' => $sortOrder],
                $testimonial,
            );
        }

        $faqGroups = [
            ['title' => 'Tamanhos e caimento', 'faq' => [
                ['question' => 'Quais tamanhos vocês têm?', 'answer' => 'Trabalhamos do 36 ao 52 na maioria das peças — a grade de cada produto aparece na página dele.'],
                ['question' => 'Como eu sei qual tamanho escolher?', 'answer' => 'Manda sua medida de quadril e busto pra gente no WhatsApp que a Cae te ajuda a escolher certinho.'],
            ]],
            ['title' => 'Trocas e devoluções', 'faq' => [
                ['question' => 'Posso trocar se não servir?', 'answer' => 'Pode! Troca sem drama em até 7 dias após o recebimento, desde que a peça esteja sem uso e com a etiqueta.'],
                ['question' => 'Como eu peço uma troca?', 'answer' => 'É só chamar a gente no WhatsApp com o número do pedido que a gente te guia pelo processo.'],
            ]],
            ['title' => 'Entrega e frete', 'faq' => [
                ['question' => 'Quanto tempo demora pra chegar?', 'answer' => 'A gente despacha em até 24h úteis depois da confirmação do pagamento. O prazo de entrega varia por região.'],
                ['question' => 'Tem frete grátis?', 'answer' => 'Sim, em compras acima do valor mínimo — a condição atual aparece no topo do site.'],
            ]],
            ['title' => 'Pagamento', 'faq' => [
                ['question' => 'Quais formas de pagamento vocês aceitam?', 'answer' => 'Combinamos o pagamento direto pelo WhatsApp — Pix, cartão ou parcelado, conforme a disponibilidade no momento da compra.'],
            ]],
            ['title' => 'Atendimento', 'faq' => [
                ['question' => 'Como eu falo com a Cae?', 'answer' => 'Direto pelo WhatsApp — é o canal mais rápido pra tirar dúvidas, ver mais fotos de uma peça ou fechar sua compra.'],
            ]],
        ];

        foreach ($faqGroups as $sortOrder => $group) {
            Section::query()->updateOrCreate(
                ['type' => SectionType::FaqGroup, 'sort_order' => $sortOrder],
                ['title' => $group['title'], 'data' => ['faq' => $group['faq']]],
            );
        }
    }
}
