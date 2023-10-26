<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\PatternAssertion;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Exception;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Base class for asserting patterns.
 */
abstract class BasePatternAssert extends Assert implements PatternAssertInterface {

  /**
   * Method that returns the assertions to be run by a particular pattern.
   *
   * Assertions extending this class need to return an array containing the
   * assertions to be run for every possible value that can be expected.
   *
   * @param string $variant
   *   The variant name that is being checked.
   *
   * @return array
   *   An array containing the assertions to be run.
   */
  abstract protected function getAssertions(string $variant): array;

  /**
   * Method that asserts the base elements of a rendered pattern.
   *
   * @param string $html
   *   The rendered pattern.
   * @param string $variant
   *   The variant being asserted.
   */
  abstract protected function assertBaseElements(string $html, string $variant): void;

  /**
   * Returns the variant of the provided rendered pattern.
   *
   * @param string $html
   *   The rendered pattern.
   *
   * @return string
   *   The variant of the rendered pattern.
   */
  protected function getPatternVariant(string $html): string {
    return 'default';
  }

  /**
   * {@inheritdoc}
   */
  public function assertPattern(array $expected, string $html): void {
    $variant = $this->getPatternVariant($html);
    $this->assertBaseElements($html, $variant);
    $assertion_map = $this->getAssertions($variant);

    // Add support for asserting the variant if the expected value is passed.
    // We add the assertion only if a custom one wasn't specified already.
    if (array_key_exists('variant', $expected) && !array_key_exists('variant', $assertion_map)) {
      $assertion_map['variant'] = [
        [self::class . '::assertEquals'],
        $variant,
        '',
      ];
    }

    $crawler = new Crawler($html);
    foreach ($expected as $name => $expected_value) {
      if (!array_key_exists($name, $assertion_map)) {
        $reflection = new \ReflectionClass($this);
        throw new Exception(sprintf('"%s" does not provide any assertion for "%s".', $reflection->getName(), $name));
      }
      if (is_array($assertion_map[$name]) && is_callable($assertion_map[$name][0])) {
        $callback = array_shift($assertion_map[$name]);
        array_unshift($assertion_map[$name], $expected_value);
        $assertion_map[$name][] = $crawler;
        call_user_func_array($callback, $assertion_map[$name]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function assertVariant(string $variant, string $html): void {
    self::assertEquals($variant, $this->getPatternVariant($html));
  }

  /**
   * Asserts the value of an attribute of a particular element.
   *
   * @param string|null $expected
   *   The expected value.
   * @param string $selector
   *   The CSS selector to find the element.
   * @param string $attribute
   *   The name of the attribute to check.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertElementAttribute($expected, string $selector, string $attribute, Crawler $crawler): void {
    $this->assertElementExists($selector, $crawler);
    $element = $crawler->filter($selector);

    if (is_null($expected)) {
      self::assertNull($element->attr($attribute));
      return;
    }

    self::assertEquals($expected, $element->attr($attribute));
  }

  /**
   * Asserts the tag of a particular element.
   *
   * @param string $expected
   *   The expected tag.
   * @param string $selector
   *   The CSS selector for the element.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertElementTag(string $expected, string $selector, Crawler $crawler): void {
    $element = $crawler->filter($selector);
    self::assertCount(1, $element, sprintf('Multiple elements found with selector "%s".', $selector));

    self::assertEquals($expected, $element->nodeName());
  }

  /**
   * Asserts the text of a particular element.
   *
   * @param string|null $expected
   *   The expected value.
   * @param string $selector
   *   The CSS selector to find the element.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertElementText($expected, string $selector, Crawler $crawler): void {
    if (is_null($expected)) {
      $this->assertElementNotExists($selector, $crawler);
      return;
    }
    $this->assertElementExists($selector, $crawler);
    $element = $crawler->filter($selector);
    $actual = trim($element->text());
    self::assertEquals($expected, $actual, sprintf(
      'Expected text value "%s" is not equal to the actual value "%s" found in the selector "%s".',
      $expected, $actual, $selector
    ));
  }

  /**
   * Asserts text is contained in a particular element.
   *
   * @param string|null $expected
   *   The expected value.
   * @param string $selector
   *   The CSS selector to find the element.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertElementTextContains(?string $expected, string $selector, Crawler $crawler): void {
    if (is_null($expected)) {
      $this->assertElementNotExists($selector, $crawler);
      return;
    }
    $this->assertElementExists($selector, $crawler);
    $element = $crawler->filter($selector);
    $actual = trim($element->text());
    self::assertStringContainsString($expected, $actual, sprintf(
      'Expected text "%s" is not present in "%s" found in the selector "%s".',
      $expected, $actual, $selector
    ));
  }

  /**
   * Asserts the rendered html of a particular element.
   *
   * @param string|null $expected
   *   The expected value.
   * @param string $selector
   *   The CSS selector to find the element.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertElementHtml($expected, string $selector, Crawler $crawler): void {
    if (is_null($expected)) {
      $this->assertElementNotExists($selector, $crawler);
      return;
    }
    $this->assertElementExists($selector, $crawler);
    $element = $crawler->filter($selector);
    self::assertEquals($expected, $element->html());
  }

  /**
   * Asserts that an element is present.
   *
   * @param string $selector
   *   The CSS selector to find the element.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertElementExists(string $selector, Crawler $crawler): void {
    $element = $crawler->filter($selector);
    self::assertCount(1, $element, sprintf(
      'Element with selector "%s" not found in the provided html.',
      $selector
    ));
  }

  /**
   * Asserts that an element is not present.
   *
   * @param string $selector
   *   The CSS selector to find the element.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertElementNotExists(string $selector, Crawler $crawler): void {
    $element = $crawler->filter($selector);
    self::assertCount(0, $element, sprintf(
      'Element with selector "%s" was found in the provided html.',
      $selector
    ));
  }

  /**
   * Asserts the image of the pattern.
   *
   * @param array|null $expected_image
   *   The expected image.
   * @param string $selector
   *   The CSS selector to find the element.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertImage(?array $expected_image, string $selector, Crawler $crawler): void {
    if (is_null($expected_image)) {
      $this->assertElementNotExists($selector, $crawler);
      return;
    }
    $this->assertElementExists($selector, $crawler);
    $element = $crawler->filter($selector);
    self::assertEquals($expected_image['alt'], $element->attr('alt'));
    if (isset($expected_image['title'])) {
      self::assertEquals($expected_image['title'], $element->attr('title'));
    }
    self::assertStringContainsString($expected_image['src'], $element->attr('src'));
  }

  /**
   * Asserts the iframe of the pattern.
   *
   * @param array|null $expected_iframe
   *   The expected iframe.
   * @param string $selector
   *   The CSS selector to find the element.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertIframe(?array $expected_iframe, string $selector, Crawler $crawler): void {
    if (is_null($expected_iframe)) {
      $this->assertElementNotExists($selector, $crawler);
      return;
    }
    $this->assertElementExists($selector, $crawler);
    $element = $crawler->filter($selector);
    self::assertEquals($expected_iframe['src'], $element->attr('src'));
  }

  /**
   * Asserts counts for given selectors.
   *
   * @param int[] $expected_counts
   *   Expected counts by selector.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The DomCrawler where to check the element.
   */
  protected function assertCounts(array $expected_counts, Crawler $crawler): void {
    foreach ($expected_counts as $selector => $expected_count) {
      $this->assertCount(
        $expected_count,
        $crawler->filter($selector),
        sprintf('Wrong count for selector "%s".', $selector),
      );
    }
  }

}
