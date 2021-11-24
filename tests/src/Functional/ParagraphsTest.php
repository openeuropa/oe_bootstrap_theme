<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\Functional;

use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests paragraphs forms.
 */
class ParagraphsTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'field_ui',
    'oe_bootstrap_theme_paragraphs',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * The administration theme name.
   *
   * @var string
   */
  protected $adminTheme = 'stark';

  /**
   * A user with administration permissions.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Rebuild the ui_pattern definitions to collect the ones provided by
    // oe_bootstrap_theme itself.
    \Drupal::service('plugin.manager.ui_patterns')->clearCachedDefinitions();

    $this->adminUser = $this->drupalCreateUser([
      'access content',
      'access administration pages',
      'administer site configuration',
      'administer users',
      'administer permissions',
      'administer content types',
      'administer node fields',
      'administer node display',
      'administer nodes',
      'bypass node access',
    ]);
    $this->drupalGet(Url::fromRoute('user.login'));
    $this->drupalLogin($this->adminUser);
    $this->drupalCreateContentType([
      'type' => 'paragraphs_test',
      'name' => 'Paragraphs Test',
    ]);
    $this->createContentTypeTestWithParagraphs();
  }

  /**
   * Test Links Block paragraphs form.
   */
  public function testLinksBlockParagraph(): void {
    $this->drupalGet('/node/add/paragraphs_test');
    $page = $this->getSession()->getPage();
    $page->pressButton('Add Links block');

    // Assert the Links Block fields appears.
    $this->assertSession()->fieldExists('field_oe_bt_paragraphs[0][subform][field_oe_links][0][uri]');
    $this->assertSession()->fieldExists('field_oe_bt_paragraphs[0][subform][field_oe_links][0][title]');
    $this->assertSession()->fieldExists('field_oe_bt_paragraphs[0][subform][field_oe_text][0][value]');
    $this->assertSession()->fieldExists('field_oe_bt_paragraphs[0][subform][oe_bt_links_block_background]');
    $this->assertSession()->fieldExists('field_oe_bt_paragraphs[0][subform][oe_bt_links_block_direction]');

    $this->submitForm([], 'Add another item');

    $values = [
      'title[0][value]' => 'Test Links block node title',
      'field_oe_bt_paragraphs[0][subform][field_oe_text][0][value]' => 'EU Links',
      'field_oe_bt_paragraphs[0][subform][field_oe_links][0][uri]' => 'https://www.example.com',
      'field_oe_bt_paragraphs[0][subform][field_oe_links][0][title]' => 'Example link number 1',
      'field_oe_bt_paragraphs[0][subform][field_oe_links][1][uri]' => 'https://www.more-example.com',
      'field_oe_bt_paragraphs[0][subform][field_oe_links][1][title]' => 'Example link number 2',
      'field_oe_bt_paragraphs[0][subform][oe_bt_links_block_background]' => 'gray',
      'field_oe_bt_paragraphs[0][subform][oe_bt_links_block_direction]' => 'vertical',
    ];

    $this->submitForm($values, 'Save');
    $this->drupalGet('/node/1');

    // Assert paragraph values are displayed correctly.
    $this->assertSession()->pageTextContains('EU Links');
    $this->assertSession()->pageTextContains('Example link number 1');
    $this->assertSession()->pageTextContains('Example link number 2');
  }

  /**
   * Test Social media follow paragraphs form.
   */
  public function testSocialMediaFollowParagraph(): void {
    $this->drupalGet('/node/add/paragraphs_test');
    $page = $this->getSession()->getPage();
    $page->pressButton('Add Social media follow');

    // Assert the Social Media Follow fields appears.
    $this->assertSession()->fieldExists('field_oe_bt_paragraphs[0][subform][field_oe_social_media_links][0][uri]');
    $this->assertSession()->fieldExists('field_oe_bt_paragraphs[0][subform][field_oe_social_media_links][0][title]');
    $this->assertSession()->fieldExists('field_oe_bt_paragraphs[0][subform][field_oe_social_media_links][0][link_type]');
    $this->assertSession()->fieldExists('field_oe_bt_paragraphs[0][subform][field_oe_title][0][value]');
    $this->assertSession()->fieldExists('field_oe_bt_paragraphs[0][subform][oe_bt_links_block_background]');
    $this->assertSession()->fieldExists('field_oe_bt_paragraphs[0][subform][field_oe_social_media_variant]');
    $this->assertSession()->fieldExists('field_oe_bt_paragraphs[0][subform][field_oe_social_media_see_more][0][uri]');
    $this->assertSession()->fieldExists('field_oe_bt_paragraphs[0][subform][field_oe_social_media_see_more][0][title]');

    $this->submitForm([], 'Add another item');

    $values = [
      'title[0][value]' => 'Test Social Media follow Links node title',
      'field_oe_bt_paragraphs[0][subform][field_oe_title][0][value]' => 'EU Social Media Follow Links',
      'field_oe_bt_paragraphs[0][subform][field_oe_social_media_links][0][uri]' => 'https://www.facebook.com',
      'field_oe_bt_paragraphs[0][subform][field_oe_social_media_links][0][title]' => 'Example Facebook',
      'field_oe_bt_paragraphs[0][subform][field_oe_social_media_links][0][link_type]' => 'facebook',
      'field_oe_bt_paragraphs[0][subform][oe_bt_links_block_background]' => 'transparent',
      'field_oe_bt_paragraphs[0][subform][field_oe_social_media_variant]' => 'horizontal',
      'field_oe_bt_paragraphs[0][subform][field_oe_social_media_see_more][0][uri]' => 'https://example.com',
      'field_oe_bt_paragraphs[0][subform][field_oe_social_media_see_more][0][title]' => 'More channels',
    ];

    $this->submitForm($values, 'Save');
    $this->drupalGet('/node/1');

    // Assert paragraph values are displayed correctly.
    $this->assertSession()->pageTextContains('EU Social Media Follow Links');
    $this->assertSession()->pageTextContains('Example Facebook');
    $this->assertSession()->pageTextContains('More channels');
  }

  /**
   * Create content type with paragraphs field.
   */
  protected function createContentTypeTestWithParagraphs(): void {
    $edit = [
      'new_storage_type' => 'field_ui:entity_reference_revisions:paragraph',
      'field_name' => 'oe_bt_paragraphs',
      'label' => 'Paragraphs',
    ];
    $this->drupalGet('/admin/structure/types/manage/paragraphs_test/fields/add-field');
    $this->submitForm($edit, 'Save and continue');
    $this->submitForm([], 'Save field settings');
    $this->submitForm([], 'Save settings');
  }

}
