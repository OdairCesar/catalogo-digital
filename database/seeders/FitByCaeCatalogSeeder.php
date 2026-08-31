<?php

namespace Database\Seeders;

use App\Enums\PageStatus;
use App\Enums\ProductAgeGroup;
use App\Enums\ProductCondition;
use App\Enums\ProductGender;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

/**
 * Dados de exemplo (placeholder) para validar visualmente o catálogo e a
 * home da Fit By Cae antes de a loja cadastrar a empresa e os produtos
 * reais pelo painel admin.
 */
class FitByCaeCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::query()->firstOrCreate(
            ['slug' => 'fit-by-cae'],
            [
                'name' => 'Fit By Cae',
                'site_name' => 'Fit By Cae',
                'whatsapp' => '(11) 90000-0000',
                'email' => 'contato@fitbycae.com.br',
                'short_description' => 'Moda fitness que valoriza corpos reais — looks para o treino e para o dia a dia.',
                'status' => PageStatus::Published,
            ],
        );

        /** @var array<string, ProductCategory> $categories */
        $categories = [];
        foreach (['Leggings', 'Tops', 'Conjuntos'] as $name) {
            $categories[$name] = ProductCategory::query()->firstOrCreate(
                ['slug' => str($name)->slug()->toString()],
                ['name' => $name, 'description' => "Peças de {$name} Fit By Cae, do 36 ao 52."],
            );
        }

        $cor = ProductAttribute::query()->firstOrCreate(['name' => 'Cor']);
        /** @var array<string, ProductAttributeValue> $corValues */
        $corValues = [];
        foreach (['Roxo', 'Preto', 'Cinza-azulado'] as $value) {
            $corValues[$value] = ProductAttributeValue::query()->firstOrCreate([
                'product_attribute_id' => $cor->id,
                'value' => $value,
            ]);
        }

        $tamanho = ProductAttribute::query()->firstOrCreate(['name' => 'Tamanho']);
        /** @var array<string, ProductAttributeValue> $tamanhoValues */
        $tamanhoValues = [];
        foreach (['P', 'M', 'G', 'GG'] as $value) {
            $tamanhoValues[$value] = ProductAttributeValue::query()->firstOrCreate([
                'product_attribute_id' => $tamanho->id,
                'value' => $value,
            ]);
        }

        $products = [
            ['title' => 'Legging Abraço', 'category' => 'Leggings', 'price' => 149.90, 'colors' => ['Roxo', 'Preto']],
            ['title' => 'Legging Recorte Lateral', 'category' => 'Leggings', 'price' => 159.90, 'colors' => ['Cinza-azulado', 'Preto']],
            ['title' => 'Top Coração', 'category' => 'Tops', 'price' => 89.90, 'colors' => ['Roxo', 'Cinza-azulado']],
            ['title' => 'Top Nadador', 'category' => 'Tops', 'price' => 79.90, 'colors' => ['Preto']],
            ['title' => 'Conjunto Abraço', 'category' => 'Conjuntos', 'price' => 219.90, 'colors' => ['Roxo', 'Preto']],
            ['title' => 'Conjunto Do Treino Pra Rua', 'category' => 'Conjuntos', 'price' => 239.90, 'colors' => ['Cinza-azulado']],
        ];

        foreach ($products as $data) {
            $product = Product::query()->firstOrCreate(
                ['slug' => str($data['title'])->slug()->toString()],
                [
                    'company_id' => $company->id,
                    'product_category_id' => $categories[$data['category']]->id,
                    'title' => $data['title'],
                    'description' => 'Looks de verdade para corpos de verdade — peça testada pela Cae, do 36 ao 52.',
                    'condition' => ProductCondition::New,
                    'gender' => ProductGender::Female,
                    'age_group' => ProductAgeGroup::Adult,
                    'base_price' => $data['price'],
                    'base_stock' => 15,
                    'status' => PageStatus::Published,
                ],
            );

            if ($product->variants()->exists()) {
                continue;
            }

            foreach ($data['colors'] as $color) {
                foreach (['P', 'M', 'G', 'GG'] as $size) {
                    $variant = $product->variants()->create([
                        'sku' => strtoupper(str($data['title'])->slug()->toString()).'-'.strtoupper(substr($color, 0, 3)).'-'.$size,
                        'stock' => 15,
                        'is_active' => true,
                    ]);

                    $variant->attributeValues()->attach([
                        $corValues[$color]->id,
                        $tamanhoValues[$size]->id,
                    ]);
                }
            }
        }
    }
}
