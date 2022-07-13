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
        [$this, 'assertLinksLabel'],
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
    $this->assertItem($expected, $file);
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
  protected function assertLinksLabel(string $expected, Crawler $crawler): void {
    $this->assertElementText($expected, 'body > div.mt-4 > div.border.rounded > div:first-child a', $crawler);
    foreach ($crawler->filter('.collapse > div a') as $node) {
      self::assertEquals($expected, $node->textContent);
    }
  }

  /**
   * Asserts elements from the file sections.
   *
   * @param array $expected
   *   The expected values.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertItem(array $expected, Crawler $crawler): void {
    $this->assertElementExists(sprintf('a[href="%s"]', $expected['url']), $crawler);
    $this->assertElementText($expected['title'], 'p.fw-medium.m-0.fs-5', $crawler);
    $this->assertItemData('p.fw-medium.m-0 + small.fw-medium', $expected, $crawler);
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
    $this->assertElementExists(sprintf('a[href="%s"]', $expected['url']), $crawler);
    $this->assertElementText($expected['title'], 'p.fw-bold.m-0', $crawler);
    $this->assertItemData('p.fw-bold.m-0 + small.fw-bold', $expected, $crawler);
  }

  /**
   * Asserts elements that are common for file and translation sections.
   *
   * @param string $data_container
   *   Value of the item data container selector.
   * @param array $expected
   *   The expected values.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertItemData(string $data_container, array $expected, Crawler $crawler): void {
    $data_container = $crawler->filter($data_container);
    // The language is the first text node.
    /** @var \DOMNode $language_node */
    $language_node = $data_container->getNode(0)->childNodes[0];
    self::assertEquals($expected['language'], $language_node->textContent);
    $this->assertElementText($expected['meta'], 'span', $data_container);

    (new IconPatternAssert())->assertPattern([
      'name' => 'download',
      'size' => 'fluid',
    ], $crawler->filter('a svg')->outerHtml());
  }

}
