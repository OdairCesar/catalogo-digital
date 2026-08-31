<?php

namespace Database\Seeders;

use App\Enums\PageStatus;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'slug' => 'criacao-de-sites',
                'name' => 'Criação de Sites',
                'title' => 'Criação de Sites',
                'subtitle' => 'Presença digital sólida e profissional para empresas de {cidade}.',
                'description' => 'Desenvolvemos sites institucionais rápidos e responsivos para empresas de {cidade}, otimizados para buscadores e pensados para transmitir credibilidade e converter visitantes em clientes.',
                'icon' => 'building',
                'benefits' => [
                    'Design responsivo para qualquer dispositivo',
                    'Otimização para Google (SEO técnico), incluindo buscas locais por empresas de {cidade}',
                    'Carregamento rápido e Core Web Vitals em dia',
                    'Atendimento próximo, com reuniões remotas para empresas de {cidade}/{uf}',
                ],
                'faq' => [
                    ['question' => 'Quanto tempo leva para ficar pronto?', 'answer' => 'Sites institucionais costumam ficar prontos entre 2 e 4 semanas, dependendo do escopo e do conteúdo já disponível.'],
                    ['question' => 'O site já vem otimizado para SEO?', 'answer' => 'Sim. Estrutura semântica, meta tags, sitemap e performance fazem parte de qualquer entrega.'],
                    ['question' => 'Vocês atendem empresas de {cidade}?', 'answer' => 'Sim, atendemos empresas de {cidade} e de toda a região {regiao}, com reuniões remotas e suporte próximo.'],
                ],
                'keywords' => ['criação de sites', 'site institucional', 'desenvolvimento web', 'site profissional'],
            ],
            [
                'slug' => 'desenvolvimento-de-sistemas-web',
                'name' => 'Desenvolvimento de Sistemas Web',
                'title' => 'Desenvolvimento de Sistemas Web',
                'subtitle' => 'Plataformas de gestão sob medida para operações internas em {cidade}.',
                'description' => 'Criamos sistemas web personalizados para empresas de {cidade}, automatizando processos internos, eliminando planilhas soltas e dando visibilidade real sobre a operação do negócio.',
                'icon' => 'cube',
                'benefits' => [
                    'Processos manuais transformados em fluxos automatizados',
                    'Acesso por permissões para cada equipe',
                    'Relatórios e dashboards em tempo real, pensados para a realidade de empresas de {cidade}',
                    'Integração com sistemas que sua empresa em {cidade} já usa',
                ],
                'faq' => [
                    ['question' => 'O sistema é feito do zero ou usa um pacote pronto?', 'answer' => 'Construímos sob medida para o seu processo, evitando pagar por funcionalidades que você não usa.'],
                    ['question' => 'Dá para integrar com o sistema que já uso?', 'answer' => 'Sim, desenvolvemos integrações via API com ERPs, planilhas e outras ferramentas.'],
                    ['question' => 'Atendem empresas de {cidade}/{uf}?', 'answer' => 'Sim, desenvolvemos sistemas web para empresas de {cidade} e de toda a região {regiao}.'],
                ],
                'keywords' => ['sistema web', 'desenvolvimento de sistemas', 'automação de processos', 'software sob medida'],
            ],
            [
                'slug' => 'desenvolvimento-de-app',
                'name' => 'Desenvolvimento de App',
                'title' => 'Desenvolvimento de Aplicativo',
                'subtitle' => 'Apps nativos e híbridos para empresas de {cidade}, do zero até a loja de aplicativos.',
                'description' => 'Desenvolvemos aplicativos mobile para empresas de {cidade}, do MVP à publicação nas lojas, com foco em performance e experiência do usuário.',
                'icon' => 'mobile',
                'benefits' => [
                    'Um único código para iOS e Android',
                    'Publicação assistida na App Store e Google Play',
                    'Notificações push e funcionamento offline',
                    'Manutenção e evolução contínua, com suporte próximo para empresas de {cidade}',
                ],
                'faq' => [
                    ['question' => 'O app funciona em iPhone e Android?', 'answer' => 'Sim, desenvolvemos com tecnologia híbrida que cobre as duas plataformas a partir de uma única base de código.'],
                    ['question' => 'Vocês cuidam da publicação nas lojas?', 'answer' => 'Sim, acompanhamos todo o processo de publicação e aprovação na App Store e na Google Play.'],
                    ['question' => 'Desenvolvem apps para empresas de {cidade}?', 'answer' => 'Sim, já desenvolvemos aplicativos para negócios de {cidade} e de toda a região {regiao}.'],
                ],
                'keywords' => ['desenvolvimento de app', 'aplicativo mobile', 'app iOS', 'app Android'],
            ],
            [
                'slug' => 'landing-pages',
                'name' => 'Landing Pages',
                'title' => 'Landing Pages',
                'subtitle' => 'Páginas de alta conversão para campanhas e lançamentos em {cidade}.',
                'description' => 'Criamos landing pages rápidas e persuasivas para as campanhas de marketing de empresas de {cidade}, com foco total em conversão e testes A/B.',
                'icon' => 'rocket',
                'benefits' => [
                    'Foco total em conversão e geração de leads',
                    'Carregamento rápido mesmo em conexões fracas',
                    'Integração com Google Ads e Meta Ads, incluindo segmentação para {cidade}',
                    'Testes A/B para melhorar a taxa de conversão',
                ],
                'faq' => [
                    ['question' => 'A landing page se integra ao meu CRM?', 'answer' => 'Sim, integramos com os principais CRMs e ferramentas de automação de marketing do mercado.'],
                    ['question' => 'Consigo medir os resultados da campanha?', 'answer' => 'Sim, configuramos analytics e pixels de conversão desde o primeiro dia no ar.'],
                    ['question' => 'Fazem landing pages para empresas de {cidade}?', 'answer' => 'Sim, criamos landing pages para campanhas de empresas de {cidade} e de toda a região {regiao}.'],
                ],
                'keywords' => ['landing page', 'página de conversão', 'página de vendas', 'marketing digital'],
            ],
            [
                'slug' => 'apis-e-integracoes',
                'name' => 'APIs & Integrações',
                'title' => 'APIs e Integrações',
                'subtitle' => 'Conectamos sistemas e automatizamos processos para empresas de {cidade}.',
                'description' => 'Construímos APIs e integrações para empresas de {cidade}, conectando sistemas, evitando retrabalho manual e mantendo os dados sincronizados entre plataformas.',
                'icon' => 'code',
                'benefits' => [
                    'Dados sincronizados entre sistemas sem retrabalho',
                    'APIs documentadas e prontas para escalar',
                    'Redução de erros causados por processos manuais',
                    'Monitoramento e alertas, com suporte próximo para empresas de {cidade}/{uf}',
                ],
                'faq' => [
                    ['question' => 'Conseguem integrar com qualquer sistema?', 'answer' => 'Na maioria dos casos, sim, desde que o sistema disponibilize alguma forma de API ou exportação de dados.'],
                    ['question' => 'Atendem empresas de {cidade}?', 'answer' => 'Sim, desenvolvemos integrações para empresas de {cidade} e de toda a região {regiao}.'],
                ],
                'keywords' => ['api', 'integração de sistemas', 'automação', 'webhook'],
            ],
            [
                'slug' => 'crm',
                'name' => 'CRM',
                'title' => 'Sistemas de CRM',
                'subtitle' => 'Gestão comercial sob medida para o funil de vendas de empresas de {cidade}.',
                'description' => 'Desenvolvemos sistemas de CRM para empresas de {cidade}, organizando o funil comercial, acompanhando clientes e aumentando a taxa de conversão da equipe de vendas.',
                'icon' => 'chart',
                'benefits' => [
                    'Funil de vendas adaptado ao seu processo comercial',
                    'Histórico completo de interações com cada cliente',
                    'Relatórios de performance da equipe de vendas',
                    'Lembretes automáticos de follow-up, com suporte próximo para empresas de {cidade}',
                ],
                'faq' => [
                    ['question' => 'O CRM se adapta ao meu processo de vendas atual?', 'answer' => 'Sim, mapeamos seu funil comercial antes de desenvolver, para o sistema refletir como sua equipe realmente vende.'],
                    ['question' => 'Desenvolvem CRM para empresas de {cidade}?', 'answer' => 'Sim, já desenvolvemos sistemas de CRM para empresas de {cidade} e de toda a região {regiao}.'],
                ],
                'keywords' => ['crm', 'gestão comercial', 'funil de vendas', 'sistema de vendas'],
            ],
        ];

        foreach ($services as $service) {
            Service::query()->firstOrCreate(
                ['slug' => $service['slug']],
                [
                    'name' => $service['name'],
                    'title' => $service['title'],
                    'subtitle' => $service['subtitle'],
                    'description' => $service['description'],
                    'icon' => $service['icon'],
                    'hero_image' => null,
                    'benefits' => $service['benefits'],
                    'faq' => $service['faq'],
                    'keywords' => $service['keywords'],
                    'status' => PageStatus::Published,
                ],
            );
        }
    }
}
