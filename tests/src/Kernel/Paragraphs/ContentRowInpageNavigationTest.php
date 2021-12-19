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
    // Create sub-paragraphs for OE Content row paragraph.
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
        3 => Paragraph::create([
          'type' => 'oe_rich_text',
          'field_oe_title' => 'Title rich text example 4',
          'field_oe_text_long' => 'Aenean at leo cursus, luctus ante a, porttitor felis. Fusce laoreet vestibulum magna, vitae tempus nulla dignissim nec. Mauris tempus, lacus eu accumsan vehicula, sem elit iaculis sapien, sit amet facilisis mi nisl nec quam. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Ut tincidunt pellentesque nunc eu bibendum. Etiam nisl dolor, hendrerit vel nisi vitae, malesuada sagittis erat. Curabitur iaculis posuere sapien, sit amet faucibus nunc auctor et. Phasellus bibendum mollis dui, ut pellentesque erat posuere sed. Mauris ut diam vel dui dignissim aliquet quis tincidunt nulla. Donec in lacus ac sem convallis pellentesque sit amet a ipsum. Vestibulum viverra mi eget magna bibendum, ut vulputate ante tristique. Praesent gravida, augue ac ullamcorper lacinia, leo orci dapibus lorem, et placerat lacus tellus ac quam.',
        ]),
        4 => Paragraph::create([
          'type' => 'oe_rich_text',
          'field_oe_title' => 'Title rich text example 5',
          'field_oe_text_long' => 'Curabitur efficitur ultrices consectetur. Nulla bibendum gravida quam a aliquet. Duis non accumsan enim. Phasellus tincidunt venenatis magna, at commodo nisi. Vivamus nec nibh vestibulum, pellentesque dolor vel, vehicula sem. Mauris lacus nulla, interdum eu justo id, sagittis tempor nulla. Phasellus faucibus, libero congue blandit sagittis, sem augue commodo enim, a tristique massa lorem vel justo. Nunc pretium consectetur felis sed consequat. Vivamus ac rutrum magna, sed laoreet turpis. Sed nisi lacus, volutpat eget mi id, maximus congue quam. Cras dictum a odio nec convallis. Mauris tincidunt felis ut eros auctor, ut varius augue rutrum. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Pellentesque posuere ornare augue. Integer ornare sagittis risus et viverra. Sed eu nulla porta, efficitur ante id, tempus nunc.',
        ]),
        5 => Paragraph::create([
          'type' => 'oe_rich_text',
          'field_oe_title' => 'Title rich text example 6',
          'field_oe_text_long' => 'Curabitur molestie tempor sem, nec posuere nisl interdum blandit. Duis vestibulum, sapien sed sodales tempor, ipsum libero consectetur dolor, eget sagittis odio lectus eu felis. Fusce suscipit augue eget lorem consectetur, a efficitur nisi ornare. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Vivamus consequat erat quis libero maximus semper. Pellentesque eget erat venenatis tellus pulvinar lacinia. Donec luctus justo dolor, quis tempor ante aliquet vel. Curabitur non luctus nibh. Maecenas commodo dolor ac bibendum fermentum. Duis id suscipit justo. Vivamus lobortis eu tellus in tincidunt. Integer tempus augue ac dui tincidunt, sed blandit lectus imperdiet. Aenean sodales elit sit amet urna lacinia, non commodo dolor porta. Donec ultrices quam eget convallis interdum. Vestibulum egestas malesuada dui sed rhoncus. Sed finibus egestas metus vitae volutpat.',
        ]),
      ];
    // Create content row paragraph with inpage navigation variant.
    $paragraph = Paragraph::create([
      'type' => 'oe_content_row',
      'field_oe_title' => 'Page content',
      'field_oe_paragraphs' => $sub_paragraphs,
      'oe_paragraphs_variant' => 'inpage_navigation',
    ]);
    $paragraph->save();

    // Testing: Inpage navigation - All paragraph types.
    $html = $this->renderParagraph($paragraph);
    $crawler = new Crawler($html);

    $this->assertCount(1, $crawler->filter('div.row'));
    $this->assertCount(1, $crawler->filter('div.col-md-3.d-none.d-md-block'));
    $this->assertCount(1, $crawler->filter('nav.position-sticky'));
    $this->assertCount(1, $crawler->filter('h5'));
    $this->assertCount(1, $crawler->filter('ul.nav.nav-pills.flex-column'));
    $this->assertCount(6, $crawler->filter('li.nav-item'));
    $this->assertCount(6, $crawler->filter('a.nav-link'));
    $this->assertCount(1, $crawler->filter('div.col-md-9'));
    $this->assertCount(6, $crawler->filter('p'));

    for ($i = 1; $i <= 6; $i++) {
      $this->assertCount(1, $crawler->filter('div#bcl-inpage-item-' . $i));
      $title = $crawler->filter('div#bcl-inpage-item-' . $i . ' h4');
      $this->assertStringContainsString(
        'Title rich text example ' . $i,
        $title->html()
      );

      $link = $crawler->filter('a[href="#bcl-inpage-item-' . $i . '"]');
      $this->assertCount(1, $link);
      $this->assertStringContainsString(
        'Title rich text example ' . $i,
        $link->html()
      );
    }
  }

}
