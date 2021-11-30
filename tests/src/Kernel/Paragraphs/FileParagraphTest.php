<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstap_theme\Kernel\Patterns;

use Drupal\Core\Site\Settings;
use Drupal\file\Entity\File;
use Drupal\oe_bootstrap_theme\ValueObject\FileValueObject;
use Drupal\Tests\oe_bootstrap_theme\Kernel\Paragraphs\ParagraphsTestBase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Test file pattern rendering.
 */
class FileParagraphTest extends ParagraphsTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'file',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('file');
  }

  /**
   * Test file pattern rendering.
   *
   * @param array $file
   *   A file array.
   *
   * @throws \Exception
   *
   * @dataProvider dataProvider
   */
  public function testFilePatternRendering(array $file) {
    $settings = Settings::getAll();
    $settings['file_public_base_url'] = 'http://example.com';
    new Settings($settings);
    $file_created = FileValueObject::fromFileEntity(File::create($file['file']));
    $file_created->setLanguageCode('English');
    $file_created->meta = '(' . format_size($file_created->getSize()) .
      ' - ' . strtoupper($file_created->getExtension()) . ')';

    // Asserting languages files exist.
    $pattern = [
      '#type' => 'pattern',
      '#id' => 'file',
      '#fields' => [
        'button_label' => 'Download',
        'file' => $file_created,
        'translation' => $file['translation'],
      ],
    ];

    $html = $this->renderRoot($pattern);
    $crawler = new Crawler($html);

    $this->assertCount(3, $crawler->filter('div.row'));
    $this->assertCount(4, $crawler->filter('small.fw-bold.m-0'));
    $this->assertCount(1, $crawler->filter('.collapse .py-3.border-bottom:nth-child(1) a[href="/example1.html"]'));
    $this->assertCount(1, $crawler->filter('.collapse .py-3.border-bottom:nth-child(2) a[href="/example2.html"]'));
    $this->assertCount(1, $crawler->filter('.collapse .pt-3 a[href="/example3.html"]'));

    $this->assertEquals('druplicon.txt', $crawler->filter('p.fw-bold.m-0')->text());
    $this->assertEquals('Other languages (3)', $crawler->filter('div.text-end a.text-underline-hover')->text());
    $this->assertEquals('English (23.99 KB - TXT)', $crawler->filter('small.fw-bold.m-0')->text());
    $this->assertEquals('български (16.3 MB - PDF)', $crawler->filter('.py-3.border-bottom:nth-child(1) small')->text());
    $this->assertEquals('Español (18.4 MB - PDF)', $crawler->filter('.py-3.border-bottom:nth-child(2) small')->text());
    $this->assertEquals('Français (16.8 MB - PDF)', $crawler->filter('.pt-3 small')->text());

    // Asserting languages files doesn't exist.
    $pattern = [
      '#type' => 'pattern',
      '#id' => 'file',
      '#fields' => [
        'button_label' => 'Download',
        'file' => $file_created,
      ],
    ];
    $html = $this->renderRoot($pattern);
    $crawler = new Crawler($html);
    $this->assertCount(0, $crawler->filter('div.row'));
    $this->assertCount(1, $crawler->filter('small.fw-bold.m-0'));

    $this->assertEquals('druplicon.txt', $crawler->filter('p.fw-bold.m-0')->text());
    $this->assertEquals('English (23.99 KB - TXT)', $crawler->filter('small.fw-bold.m-0')->text());
    $this->assertEquals('http://example.com/sample/druplicon.txt', $crawler->filter('.text-underline-hover')->attr('href'));
    $this->assertEquals('/assets/icons/bootstrap-icons.svg#file-text-fill', $crawler->filter('.text-secondary use')->attr('xlink:href'));
  }

  /**
   * Data provider for testFilePatternRendering.
   *
   * @return array
   *   An array of test data arrays with assertions.
   */
  public function dataProvider(): array {
    $data = [];
    $data['file'] = [
      'fields' => [
        'file' => [
          'uid' => '1',
          'filename' => 'druplicon.txt',
          'title' => 'druplicon.txt',
          'filemime' => 'text/plain',
          'uri' => 'public://sample/druplicon.txt',
          'filesize' => '24564',
          'languageCode' => 'English',
        ],
        'translation' => [
          'label' => [
            'label' => "Other languages (3)",
          ],
          'items' => [
            [
              'language' => 'български',
              'meta' => '(16.3 MB - PDF)',
              'download' => [
                'label' => 'Download',
                'path' => '/example1.html',
              ],
            ],
            [
              'language' => 'Español',
              'meta' => '(18.4 MB - PDF)',
              'download' => [
                'label' => 'Download',
                'path' => '/example2.html',
              ],
            ],
            [
              'language' => 'Français',
              'meta' => '(16.8 MB - PDF)',
              'download' => [
                'label' => 'Download',
                'path' => '/example3.html',
              ],
            ],
          ],
        ],
      ],
    ];

    return $data;
  }

}
