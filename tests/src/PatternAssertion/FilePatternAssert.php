<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\PatternAssertion;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the file pattern.
 */
class FilePatternAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions(string $variant): array {
    return [
      'file' => [
        [$this, 'assertFile'],
      ],
      'translations' => [
        [$this, 'assertTranslations'],
      ],
      'link_label' => [
        [$this, 'assertDownloadLinksLabel'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $crawler = new Crawler($html);
    // No titles should be rendered in the pattern.
    $this->assertElementNotExists('h2', $crawler);
    // Only one icon should be rendered. The type will be asserted in the file
    // assert.
    $this->assertElementExists('svg.icon--2xl', $crawler);
    $this->assertElementExists('svg.icon--file', $crawler);
    // Only one wrapper element is present.
    $this->assertElementExists('body > .mt-4', $crawler);
    $this->assertElementExists('body > div.mt-4 > div.border.rounded', $crawler);
  }

  /**
   * Asserts the main file section of the pattern.
   *
   * @param array $expected
   *   The expected file section values.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertFile(array $expected, Crawler $crawler): void {
    $file = $crawler->filter('body > div.mt-4 > div.border.rounded > div:first-child');
    self::assertCount(1, $file);

    (new IconPatternAssert())->assertPattern([
      'name' => $expected['icon'],
      'size' => '2xl',
    ], $crawler->filter('svg.icon--file')->outerHtml());

    $this->assertDownloadLink($expected, $crawler);
    $this->assertElementText($expected['title'], 'p.fw-medium.m-0.fs-5', $crawler);
    $this->assertItemData($expected, $crawler->filter('p.fw-medium.m-0 + small.fw-medium'));
  }

  /**
   * Asserts the translations section of the pattern.
   *
   * @param array|null $expected
   *   The expected translation section values, or NULL if no translations are
   *   expected.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertTranslations(?array $expected, Crawler $crawler): void {
    // This selector targets the DIVs that wrap the main file and the
    // translations. When no translations are present, there should be only one
    // node.
    $container_selector = 'body > div.mt-4 > div.border.rounded > div';

    if ($expected === NULL) {
      // When no translations are present, only one div exists inside the
      // container.
      $this->assertElementExists($container_selector, $crawler);
      $this->assertElementNotExists('.collapse', $crawler);
      return;
    }

    $containers = $crawler->filter($container_selector);
    self::assertCount(2, $containers);
    // The second node wraps all the translations section.
    $wrapper = $containers->eq(1);
    $this->assertElementText(sprintf('Other languages (%s)', count($expected)), '.text-end a', $wrapper);

    $translation_nodes = $wrapper->filter('.collapse > div');
    self::assertSameSize($expected, $translation_nodes);
    foreach ($expected as $index => $translation) {
      $this->assertTranslationItem($translation, $translation_nodes->eq($index));
    }
  }

  /**
   * Asserts the label used for the download links.
   *
   * @param string $expected
   *   The expected label.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertDownloadLinksLabel(string $expected, Crawler $crawler): void {
    $this->assertElementText($expected, 'body > div.mt-4 > div.border.rounded > div:first-child a', $crawler);
    foreach ($crawler->filter('.collapse > div a') as $node) {
      self::assertEquals($expected, $node->textContent);
    }
  }

  /**
   * Asserts elements from the translation sections.
   *
   * @param array $expected
   *   The expected values.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertTranslationItem(array $expected, Crawler $crawler): void {
    $this->assertDownloadLink($expected, $crawler);
    $this->assertElementText($expected['title'], 'p.fw-bold.m-0', $crawler);
    $this->assertItemData($expected, $crawler->filter('p.fw-bold.m-0 + small.fw-bold'));
  }

  /**
   * Asserts a single item data section.
   *
   * @param array $expected
   *   The expected values.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertItemData(array $expected, Crawler $crawler): void {
    // The language is the first text node.
    /** @var \DOMNode $language_node */
    $language_node = $crawler->getNode(0)->childNodes[0];
    self::assertEquals($expected['language'], $language_node->textContent);
    $this->assertElementText($expected['meta'], 'span', $crawler);
  }

  /**
   * Asserts the file download link.
   *
   * @param array $expected
   *   The expected values.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertDownloadLink(array $expected, Crawler $crawler): void {
    $link_selector = sprintf('a[href="%s"]', $expected['url']);
    $this->assertElementExists($link_selector, $crawler);

    (new IconPatternAssert())->assertPattern([
      'name' => 'download',
      'size' => 'fluid',
    ], $crawler->filter($link_selector . ' svg')->outerHtml());
  }

}
