<?php

namespace Drupal\search_api_block\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Utility\Token;
use Drupal\search_api_block\Form\SearchApiForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Search API form' block.
 *
 * @Block(
 *   id = "search_api_form_block",
 *   admin_label = @Translation("Search API form"),
 *   category = @Translation("Forms"),
 *   context_definitions = {
 *     "entity" = @ContextDefinition("entity", required = FALSE, label=@Translation("Entity"))
 *   }
 * )
 */
class SearchBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected Token $token;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a new SearchLocalTask.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    Token $token,
    FormBuilderInterface $form_builder,
    ModuleHandlerInterface $module_handler,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->token = $token;
    $this->formBuilder = $form_builder;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('token'),
      $container->get('form_builder'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $url = $this->configuration['action_url'] ?? NULL;
    $action_method = $this->configuration['action_method'] ?? 'get';
    $input_name = $this->configuration['input_name'] ?? '';
    $input_placeholder = $this->configuration['input_placeholder'] ?? '';
    $submit_value = $this->configuration['submit_value'] ?? '';
    $input_label = $this->configuration['input_label'] ?? '';
    $input_label_visibility = $this->configuration['input_label_visibility'] ?? 'invisible';
    $pass_get_params = $this->configuration['pass_get_params'] ?? FALSE;

    return $this->formBuilder->getForm(
      SearchApiForm::class,
      $this->replaceTokenValue($url),
      $action_method,
      $this->replaceTokenValue($input_name),
      $this->replaceTokenValue($input_placeholder),
      $this->replaceTokenValue($submit_value),
      $this->replaceTokenValue($input_label),
      $input_label_visibility,
      $pass_get_params
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'action_url' => '',
      'action_method' => 'get',
      'input_name' => '',
      'input_placeholder' => '',
      'submit_value' => '',
      'pass_get_params' => FALSE,
      'input_label' => '',
      'input_label_visibility' => 'invisible',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $form['action_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search page'),
      '#required' => TRUE,
      '#description' => $this->t(
        'The search page that the form submits to (e.g. /search). This input
          supports token values.'
      ),
      '#default_value' => $this->configuration['action_url'],
    ];

    $form['action_method'] = [
      '#type' => 'select',
      '#title' => $this->t('Submit method'),
      '#required' => TRUE,
      '#options' => [
        'get' => $this->t('GET'),
        'post' => $this->t('POST'),
      ],
      '#description' => $this->t('The method used to submit the form.'),
      '#default_value' => $this->configuration['action_method'],
    ];

    $form['input_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Input name'),
      '#required' => FALSE,
      '#placeholder' => 'keys',
      '#description' => $this->t('The name of the search input. This should be the name of the exposed filter'),
      '#default_value' => $this->configuration['input_name'],
    ];

    $form['pass_get_params'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Pass GET parameters'),
      '#required' => FALSE,
      '#placeholder' => 'Search',
      '#description' => $this->t('If this box is checked, any GET parameter added to the Search page URL will be passed as a hidden parameter.'),
      '#default_value' => $this->configuration['pass_get_params'],
    ];

    $form['customization'] = [
      '#type' => 'details',
      '#title' => $this->t('Customization'),
    ];

    $form['customization']['input_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Input label'),
      '#required' => FALSE,
      '#placeholder' => 'Search',
      '#description' => $this->t('The label of the search input.'),
      '#default_value' => $this->configuration['input_label'],
    ];

    $form['customization']['input_label_visibility'] = [
      '#type' => 'select',
      '#title' => $this->t('Label visibility'),
      '#required' => FALSE,
      '#options' => [
        'invisible' => $this->t('Invisible'),
        'before' => $this->t('Before'),
        'after' => $this->t('After'),
        'attribute' => $this->t('Attribute'),
      ],
      '#description' => $this->t('The visibility of the label.'),
      '#default_value' => $this->configuration['input_label_visibility'],
    ];

    $form['customization']['input_placeholder'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Input placeholder'),
      '#required' => FALSE,
      '#description' => $this->t('The placeholder of the search input.'),
      '#default_value' => $this->configuration['input_placeholder'],
    ];

    $form['customization']['submit_value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Submit label'),
      '#required' => FALSE,
      '#placeholder' => 'Search',
      '#description' => $this->t('The value of the submit button.'),
      '#default_value' => $this->configuration['submit_value'],
    ];

    if ($this->moduleHandler->moduleExists('token')) {
      $form['replacements'] = [
        '#theme' => 'token_tree_link',
        '#token_types' => ['entity'],
        '#global_types' => TRUE,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    // $values = $form_state->getValues();
    $this->configuration['action_url'] = $form_state->getValue('action_url');
    $this->configuration['action_method'] = $form_state->getValue('action_method');
    $this->configuration['input_name'] = $form_state->getValue('input_name');
    $this->configuration['input_placeholder'] = $form_state->getValue('customization')['input_placeholder'];
    $this->configuration['submit_value'] = $form_state->getValue('customization')['submit_value'];
    $this->configuration['pass_get_params'] = $form_state->getValue('pass_get_params');
    $this->configuration['input_label'] = $form_state->getValue('customization')['input_label'];
    $this->configuration['input_label_visibility'] = $form_state->getValue('customization')['input_label_visibility'];
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    if (($value = $this->replaceTokenValue($form_state->getValue('action_url')))
      && strpos($value, '/') !== 0
    ) {
      $form_state->setErrorByName(
        'action_url', $this->t(
          "The path '%path' has to start with a slash.", [
            '%path' => $value,
          ])
      );
    }
  }

  /**
   * Replace token value string.
   *
   * @param string|null $value
   *   The value that can contain the token.
   *
   * @return string|null
   *   The fully qualified value with the token replaced.
   *
   * @throws \Drupal\Component\Plugin\Exception\ContextException
   */
  protected function replaceTokenValue(?string $value): ?string {
    if (isset($value)) {
      $context = [];

      if (($entity = $this->getContext('entity')) && $entity->hasContextValue()) {
        $entity_value = $entity->getContextValue();
        $context[$entity_value->getEntityTypeId()] = $entity_value;
      }

      $value = $this->token->replace(
        $value,
        $context,
        ['clear' => TRUE]
      );
    }

    return $value;
  }

}
