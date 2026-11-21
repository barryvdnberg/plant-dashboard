<?php

use App\Rules\ImageDirectory;
use Illuminate\Support\Facades\Validator;

it('rejects traversal and active content in imported image paths', function (string $path): void {
    expect(Validator::make(['path' => $path], ['path' => [new ImageDirectory()]])->fails())->toBeTrue();
})->with([
    'uploads/images/../../private/secret.png',
    'uploads/images/../secret.png',
    'uploads/images/%2e%2e/secret.png',
    'uploads/images/..\\secret.png',
    'uploads/images/evil.svg',
    'uploads/images/evil.php',
    'uploads/images/',
    "uploads/images/test.png\0.php",
]);

it('accepts image paths beneath the upload directory', function (): void {
    $validator = Validator::make(
        ['image_path' => 'uploads/images/plants/tulip.png'],
        ['image_path' => [new ImageDirectory()]],
    );

    expect($validator->passes())->toBeTrue();
});

it('rejects scalar image paths outside the upload directory', function (): void {
    $validator = Validator::make(
        ['image_path' => 'storage/images/tulip.png'],
        ['image_path' => [new ImageDirectory()]],
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('image_path'))->toContain('uploads/images/');
});

it('rejects wrong data types without throwing', function (mixed $value): void {
    $messages = [];

    (new ImageDirectory())->validate('image_path', $value, function (string $message) use (&$messages): void {
        $messages[] = $message;
    });

    expect($messages)->toHaveCount(1)
        ->and($messages[0])->toContain('uploads/images/');
})->with([
    'empty string' => '',
    'integer' => 42,
    'array' => [['uploads/images/tulip.png']],
    'null' => null,
]);
