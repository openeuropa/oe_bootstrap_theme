<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_bootstrap_theme_helper\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\oe_bootstrap_theme_helper\Plugin\Filter\BootstrapTable;

/**
 * Tests the bootstrap table filter.
 */
class BootstrapTableFilterTest extends UnitTestCase {

  /**
   * Tests the process method.
   *
   * @param array $settings
   *   The plugin settings.
   * @param string $html
   *   The filter input.
   * @param string $expected
   *   The expected filter output.
   *
   * @dataProvider processDataProvider
   */
  public function testProcess(array $settings, string $html, string $expected): void {
    $filter = new BootstrapTable(['settings' => $settings], 'oe_bootstrap_theme_table', ['provider' => 'test']);

    $processed_text = $filter->process($html, NULL)->getProcessedText();
    $this->assertEquals($expected, $processed_text);
  }

  /**
   * Data provider for testProcess().
   *
   * @return array[]
   *   A list of test scenarios.
   */
  public function processDataProvider(): array {
    return [
      'No tables' => [
        ['responsive' => ''],
        '<p>A sample text</p>',
        '<p>A sample text</p>',
      ],
      'Table with no classes' => [
        ['responsive' => ''],
        '<table><tr><td>Lorem</td><td>Ipsum</td></tr></table>',
        '<table class="table"><tr><td>Lorem</td><td>Ipsum</td></tr></table>',
      ],
      'Table with existing classes' => [
        ['responsive' => ''],
        '<table class=" table-primary "><tr><td>Lorem</td><td>Ipsum</td></tr></table>',
        '<table class="table-primary table"><tr><td>Lorem</td><td>Ipsum</td></tr></table>',
      ],
      'Table with existing table class' => [
        ['responsive' => ''],
        '<table class=" table  table-primary"><tr><td>Lorem</td><td>Ipsum</td></tr></table>',
        '<table class=" table  table-primary"><tr><td>Lorem</td><td>Ipsum</td></tr></table>',
      ],
      'Table with parameters' => [
        ['responsive' => ''],
        '<table id="test"><tr><td>Lorem</td><td>Ipsum</td></tr></table>',
        '<table id="test" class="table"><tr><td>Lorem</td><td>Ipsum</td></tr></table>',
      ],
      'Nested table' => [
        ['responsive' => ''],
        '<table><tr><td>Lorem</td><td><table><tr><td>Nested</td><td>Table</td></tr></table></td></tr></table>',
        '<table class="table"><tr><td>Lorem</td><td><table class="table"><tr><td>Nested</td><td>Table</td></tr></table></td></tr></table>',
      ],
      'Responsive - always' => [
        ['responsive' => 'always'],
        '<table><tr><td>Lorem</td><td>Ipsum</td></tr></table>',
        '<div class="table-responsive"><table class="table"><tr><td>Lorem</td><td>Ipsum</td></tr></table></div>',
      ],
      'Responsive - small' => [
        ['responsive' => 'sm'],
        '<table><tr><td>Lorem</td><td>Ipsum</td></tr></table>',
        '<div class="table-responsive-sm"><table class="table"><tr><td>Lorem</td><td>Ipsum</td></tr></table></div>',
      ],
      'Responsive - medium' => [
        ['responsive' => 'md'],
        '<table><tr><td>Lorem</td><td>Ipsum</td></tr></table>',
        '<div class="table-responsive-md"><table class="table"><tr><td>Lorem</td><td>Ipsum</td></tr></table></div>',
      ],
      'Responsive - large' => [
        ['responsive' => 'lg'],
        '<table><tr><td>Lorem</td><td>Ipsum</td></tr></table>',
        '<div class="table-responsive-lg"><table class="table"><tr><td>Lorem</td><td>Ipsum</td></tr></table></div>',
      ],
      'Responsive - extra large' => [
        ['responsive' => 'xl'],
        '<table><tr><td>Lorem</td><td>Ipsum</td></tr></table>',
        '<div class="table-responsive-xl"><table class="table"><tr><td>Lorem</td><td>Ipsum</td></tr></table></div>',
      ],
      'Responsive - extra extra large' => [
        ['responsive' => 'xxl'],
        '<table><tr><td>Lorem</td><td>Ipsum</td></tr></table>',
        '<div class="table-responsive-xxl"><table class="table"><tr><td>Lorem</td><td>Ipsum</td></tr></table></div>',
      ],
      'Responsive - nested table' => [
        ['responsive' => 'always'],
        '<table><tr><td>Lorem</td><td><table><tr><td>Nested</td><td>Table</td></tr></table></td></tr></table>',
        '<div class="table-responsive"><table class="table"><tr><td>Lorem</td><td><table class="table"><tr><td>Nested</td><td>Table</td></tr></table></td></tr></table></div>',
      ],
    ];
  }

}
