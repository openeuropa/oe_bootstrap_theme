<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\Functional;

use Drupal\Core\Url;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests paragraphs forms.
 */
class ParagraphsTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'config',
    'system',
    'node',
    'field_ui',
    'oe_bootstrap_theme_paragraphs',
    'oe_bootstrap_theme_helper',
    'oe_content_timeline_field',
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
    $this->addParagraphsField();
  }

  /**
   * Test Links Block paragraphs form.
   */
  public function testLinksBlockParagraph(): void {
    $this->drupalGet('/node/add/paragraphs_test');
    $page = $this->getSession()->getPage();
    $page->pressButton('Add Links block');

    // Assert the Links Block fields appears.
    $this->assertSession()->fieldExists('oe_bt_paragraphs[0][subform][field_oe_links][0][uri]');
    $this->assertSession()->fieldExists('oe_bt_paragraphs[0][subform][field_oe_links][0][title]');
    $this->assertSession()->fieldExists('oe_bt_paragraphs[0][subform][field_oe_text][0][value]');
    $this->assertSession()->fieldExists('oe_bt_paragraphs[0][subform][oe_bt_links_block_background]');
    $this->assertSession()->fieldExists('oe_bt_paragraphs[0][subform][oe_bt_links_block_orientation]');

    $this->submitForm([], 'Add another item');

    $values = [
      'title[0][value]' => 'Test Links block node title',
      'oe_bt_paragraphs[0][subform][field_oe_text][0][value]' => 'EU Links',
      'oe_bt_paragraphs[0][subform][field_oe_links][0][uri]' => 'https://www.example.com',
      'oe_bt_paragraphs[0][subform][field_oe_links][0][title]' => 'Example link number 1',
      'oe_bt_paragraphs[0][subform][field_oe_links][1][uri]' => 'https://www.more-example.com',
      'oe_bt_paragraphs[0][subform][field_oe_links][1][title]' => 'Example link number 2',
      'oe_bt_paragraphs[0][subform][oe_bt_links_block_background]' => 'gray',
      'oe_bt_paragraphs[0][subform][oe_bt_links_block_orientation]' => 'vertical',
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
    $this->assertSession()->fieldExists('oe_bt_paragraphs[0][subform][field_oe_social_media_links][0][uri]');
    $this->assertSession()->fieldExists('oe_bt_paragraphs[0][subform][field_oe_social_media_links][0][title]');
    $this->assertSession()->fieldExists('oe_bt_paragraphs[0][subform][field_oe_social_media_links][0][link_type]');
    $this->assertSession()->fieldExists('oe_bt_paragraphs[0][subform][field_oe_title][0][value]');
    $this->assertSession()->fieldExists('oe_bt_paragraphs[0][subform][oe_bt_links_block_background]');
    $this->assertSession()->fieldExists('oe_bt_paragraphs[0][subform][field_oe_social_media_variant]');
    $this->assertSession()->fieldExists('oe_bt_paragraphs[0][subform][field_oe_social_media_see_more][0][uri]');
    $this->assertSession()->fieldExists('oe_bt_paragraphs[0][subform][field_oe_social_media_see_more][0][title]');

    $this->submitForm([], 'Add another item');

    $values = [
      'title[0][value]' => 'Test Social Media follow Links node title',
      'oe_bt_paragraphs[0][subform][field_oe_title][0][value]' => 'EU Social Media Follow Links',
      'oe_bt_paragraphs[0][subform][field_oe_social_media_links][0][uri]' => 'https://www.facebook.com',
      'oe_bt_paragraphs[0][subform][field_oe_social_media_links][0][title]' => 'Example Facebook',
      'oe_bt_paragraphs[0][subform][field_oe_social_media_links][0][link_type]' => 'facebook',
      'oe_bt_paragraphs[0][subform][oe_bt_links_block_background]' => 'transparent',
      'oe_bt_paragraphs[0][subform][field_oe_social_media_variant]' => 'horizontal',
      'oe_bt_paragraphs[0][subform][field_oe_social_media_see_more][0][uri]' => 'https://example.com',
      'oe_bt_paragraphs[0][subform][field_oe_social_media_see_more][0][title]' => 'More channels',
    ];

    $this->submitForm($values, 'Save');
    $this->drupalGet('/node/1');

    // Assert paragraph values are displayed correctly.
    $this->assertSession()->pageTextContains('EU Social Media Follow Links');
    $this->assertSession()->pageTextContains('Example Facebook');
    $this->assertSession()->pageTextContains('More channels');
  }

  /**
   * Adds a field to a paragraph.
   */
  protected function addParagraphsField() {
    // Add a paragraphs field.
    $field_storage = FieldStorageConfig::create([
      'field_name' => 'oe_bt_paragraphs',
      'entity_type' => 'node',
      'type' => 'entity_reference_revisions',
      'cardinality' => '-1',
      'settings' => [
        'target_type' => 'paragraph',
      ],
    ]);
    $field_storage->save();
    $field = FieldConfig::create([
      'field_storage' => $field_storage,
      'bundle' => 'paragraphs_test',
      'settings' => [
        'handler' => 'default:paragraph',
        'handler_settings' => ['target_bundles' => NULL],
      ],
    ]);
    $field->save();

    $form_display = \Drupal::service('entity_display.repository')->getFormDisplay('node', 'paragraphs_test');
    $form_display = $form_display->setComponent('oe_bt_paragraphs', ['type' => 'paragraphs']);
    $form_display->save();

    $view_display = \Drupal::service('entity_display.repository')->getViewDisplay('node', 'paragraphs_test');
    $view_display->setComponent('oe_bt_paragraphs', ['type' => 'entity_reference_revisions_entity_view']);
    $view_display->save();
  }

}
