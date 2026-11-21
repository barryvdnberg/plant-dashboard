<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\FileUpload;

class ImageUpload extends FileUpload
{
    public static function make(?string $name = null): static
    {
        /** @var static $field */
        $field = parent::make($name);

        return $field
            ->label(__('attributes.Image_paths'))
            ->image()
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif'])
            ->preventFilePathTampering()
            ->directory("uploads/images")
            ->maxSize(4096)
            ->imageEditor()
            ->imageAspectRatio('16:9')
            ->automaticallyCropImagesToAspectRatio()
            ->automaticallyResizeImagesToHeight('1080')
            ->automaticallyResizeImagesToWidth('1920')
            ->openable()
            ->columnSpanFull();
    }
}
