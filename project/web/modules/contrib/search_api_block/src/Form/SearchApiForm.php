<?php

namespace Drupal\search_api_block\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Builds the search form for the search block.
 *
 * @internal
 */
class SearchApiForm extends FormBase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a new SearchApiForm.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(ConfigFactoryInterface $config_factory, RendererInterface $renderer) {
    $this->configFactory = $config_factory;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'search_api_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $action_url = NULL, $action_method = 'get', $input_name = 'keys', $input_placeholder = '', $submit_value = '', $input_label = '', $input_label_visibility = 'invisible', $pass_get_params = FALSE) {

    if (!$action_url) {
      $form['message'] = [
        '#markup' => $this->t('Search is currently disabled'),
      ];
      return $form;
    }

    $form['#action'] = Url::fromUri('internal:' . $action_url)->toString();
    $form['#method'] = $action_method;

    if ($pass_get_params) {
      $url_components = parse_url($form['#action']);
      if (!empty($url_components['query'])) {
        $params = [];
        parse_str($url_components['query'], $params);

        foreach ($params as $name => $param) {
          if (is_array($param)) {
            foreach ($param as $index_param => $param_value) {
              $param_name = $name . '[' . $index_param . ']';
              $form[$param_name] = [
                '#type' => 'hidden',
                '#value' => $param_value,
              ];
            }
          }
          else {
            $form[$name] = [
              '#type' => 'hidden',
              '#value' => $param,
            ];
          }
        }
      }
    }

    $form[$input_name] = [
      '#type' => 'search',
      '#title' => !empty($input_label) ? $input_label : $this->t('Search'),
      '#title_display' => !empty($input_label_visibility) ? $input_label_visibility : 'invisible',
      '#size' => 15,
      '#default_value' => '',
      '#placeholder' => $input_placeholder,
      '#attributes' => ['title' => $this->t('Enter the terms you wish to search for.')],
      '#search_api_block' => TRUE,
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => !empty($submit_value) ? $submit_value : $this->t('Search'),
      // Prevent op from showing up in the query string.
      '#name' => '',
      '#search_api_block' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // This form submits to the search page, so processing happens there.
  }

}
