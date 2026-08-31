<?php

namespace App\Filament\Resources\Testimonials\Pages;

use App\Filament\Concerns\SetsSectionTypeOnCreate;
use App\Filament\Resources\Testimonials\TestimonialResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTestimonial extends CreateRecord
{
    use SetsSectionTypeOnCreate;

    protected static string $resource = TestimonialResource::class;
}
