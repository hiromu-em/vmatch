<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Vmatch\FormValidation;

require_once __DIR__ . '/../vendor/autoload.php';

class FormValidationTest extends TestCase
{
    public static function invalidImageProvider(): array
    {
        return [
            'Invalid file type' => [[
                'name' => 'profile.gif',
                'type' => 'image/gif',
                'size' => 1024 * 1024,
                'error' => UPLOAD_ERR_OK,
            ]],
            'File size too large' => [[
                'name' => 'profile.jpeg',
                'type' => 'image/jpeg',
                'size' => 5 * 1024 * 1024,
                'error' => UPLOAD_ERR_OK,
            ]],
            'Upload error' => [[
                'name' => 'profile.png',
                'type' => 'image/png',
                'size' => 1024 * 1024,
                'error' => UPLOAD_ERR_CANT_WRITE,
            ]],
        ];
    }

    /** 
     * プロフィール画像の検証テスト（無効な画像） 
     * */
    #[DataProvider('invalidImageProvider')]
    public function testValidationImageWithInvalidImage(array $invalidImage): void
    {
        $formValidation = new FormValidation();

        $this->assertTrue($formValidation->validationImage($invalidImage));
    }

    public static function validImageProvider(): array
    {
        return [
            'Valid JPEG image' => [[
                'name' => 'profile.jpeg',
                'type' => 'image/jpeg',
                'size' => 1024 * 1024,
                'error' => UPLOAD_ERR_OK,
            ]],
            'Valid PNG image' => [[
                'name' => 'profile.png',
                'type' => 'image/png',
                'size' => 2 * 1024 * 1024,
                'error' => UPLOAD_ERR_OK,
            ]],
            'Valid JPG image' => [[
                'name' => 'profile.jpg',
                'type' => 'image/jpg',
                'size' => 500 * 1024,
                'error' => UPLOAD_ERR_OK,
            ]],
        ];
    }
    
    /** 
     * プロフィール画像の検証テスト（有効な画像） 
     * */
    #[DataProvider('validImageProvider')]
    public function testValidationImageWithValidImage(array $validImage): void
    {
        $formValidation = new FormValidation();

        $this->assertFalse($formValidation->validationImage($validImage));
    }
}