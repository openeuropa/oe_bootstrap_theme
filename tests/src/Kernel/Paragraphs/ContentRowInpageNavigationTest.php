<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\Kernel\Paragraphs;

use Drupal\paragraphs\Entity\Paragraph;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Tests the "Content row" paragraph, "Inpage navigation" variant.
 */
class ContentRowInpageNavigationTest extends ParagraphsTestBase {

  /**
   * Tests the basic rendering of the paragraph type.
   */
  public function testBasicRendering(): void {
    $sub_paragraphs =
      [
        0 => Paragraph::create([
          'type' => 'oe_rich_text',
          'field_oe_title' => 'Title rich text example 1',
          'field_oe_text_long' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque ornare et elit a dictum. Maecenas lacinia eros quis eros iaculis, sit amet bibendum massa facilisis. Integer arcu nisl, fringilla nec quam vel, tincidunt maximus ex. Suspendisse ac arcu efficitur, feugiat tellus vel, viverra sapien. Etiam vitae condimentum lorem. Nulla congue ligula lacinia efficitur tempus. Duis vitae auctor enim. Nulla iaculis, diam et sagittis scelerisque, est mauris luctus sem, a imperdiet lacus diam eu dui. Morbi accumsan, augue eu gravida elementum, libero mi blandit odio, eu fringilla nunc ipsum non tellus. Suspendisse dapibus elit at lobortis pretium. Quisque vestibulum ut purus sit amet molestie. Sed eget volutpat justo, vel varius augue. Vestibulum vel risus facilisis, feugiat sem aliquam, lobortis ante.',
        ]),
        1 => Paragraph::create([
          'type' => 'oe_rich_text',
          'field_oe_title' => 'Title rich text example 2',
          'field_oe_text_long' => 'Cras eu vestibulum dolor. Donec varius suscipit diam non faucibus. Praesent a facilisis quam. Nulla tempor est arcu, nec facilisis magna semper vel. Maecenas eu dui ac orci consequat pulvinar. Pellentesque sit amet vulputate ante. Curabitur eget sapien orci. In sed consequat turpis, sed eleifend sapien. Vivamus dignissim sollicitudin nulla, vitae scelerisque enim placerat vitae. Sed suscipit mi et est porta, eget pharetra ante consequat. Morbi a posuere metus. Etiam eu pretium orci. Nunc dignissim finibus augue, ac mollis lorem mollis at. Donec quam lectus, aliquam eget efficitur ut, lobortis fringilla felis.',
        ]),
        2 => Paragraph::create([
          'type' => 'oe_rich_text',
          'field_oe_title' => 'Title rich text example 3',
          'field_oe_text_long' => 'Aliquam tristique vulputate lorem nec imperdiet. Phasellus ac urna dui. Pellentesque turpis orci, bibendum id mauris eu, commodo interdum mi. Nulla quis blandit enim, eu lacinia ligula. Fusce lorem odio, fermentum ullamcorper felis accumsan, gravida porttitor velit. Vivamus a pretium lacus. Ut commodo tortor arcu, non tristique enim congue sit amet. Aliquam lobortis sapien est, at gravida enim eleifend sit amet. Aenean ac massa rhoncus, luctus purus at, vulputate ipsum. Donec at tortor a elit porta suscipit a ut lectus. Nunc et diam non eros laoreet laoreet. Phasellus eu metus porta, laoreet tortor nec, placerat tellus. Curabitur suscipit elementum ligula eget tincidunt. Quisque at pharetra purus.',
        ]),
      ];

    $paragraph = Paragraph::create([
      'type' => 'oe_content_row',
      'field_oe_title' => 'Page content',
      'field_oe_paragraphs' => $sub_paragraphs,
      'oe_paragraphs_variant' => 'inpage_navigation',
    ]);
    $paragraph->save();

    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('div.row'));
    $this->assertCount(1, $crawler->filter('div.col-md-3.d-none.d-md-block'));
    $this->assertCount(1, $crawler->filter('nav.position-sticky'));
    $this->assertCount(1, $crawler->filter('h5'));
    $this->assertCount(1, $crawler->filter('ul.nav.nav-pills.flex-column'));
    $this->assertCount(3, $crawler->filter('li.nav-item'));
    $this->assertCount(3, $crawler->filter('a.nav-link'));
    $this->assertCount(1, $crawler->filter('div.col-md-9'));
    $this->assertCount(3, $crawler->filter('p'));

    $this->assertCount(1, $crawler->filter('div#bcl-inpage-item-1'));
    $title = $crawler->filter('div#bcl-inpage-item-1 h4');
    $this->assertStringContainsString(
      'Title rich text example 1',
      $title->html()
    );

    $link = $crawler->filter('a[href="#bcl-inpage-item-1"]');
    $this->assertCount(1, $link);
    $this->assertStringContainsString(
      'Title rich text example 1',
      $link->html()
    );

    $this->assertCount(1, $crawler->filter('div#bcl-inpage-item-2'));
    $title = $crawler->filter('div#bcl-inpage-item-2 h4');
    $this->assertStringContainsString(
      'Title rich text example 2',
      $title->html()
    );

    $link = $crawler->filter('a[href="#bcl-inpage-item-2"]');
    $this->assertCount(1, $link);
    $this->assertStringContainsString(
      'Title rich text example 2',
      $link->html()
    );

    $this->assertCount(1, $crawler->filter('div#bcl-inpage-item-3'));
    $title = $crawler->filter('div#bcl-inpage-item-3 h4');
    $this->assertStringContainsString(
      'Title rich text example 3',
      $title->html()
    );

    $link = $crawler->filter('a[href="#bcl-inpage-item-3"]');
    $this->assertCount(1, $link);
    $this->assertStringContainsString(
      'Title rich text example 3',
      $link->html()
    );
  }

}
