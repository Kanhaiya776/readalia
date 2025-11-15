<?php

/**
 * @file
 */

/**
 * Implements hook_views_data().
 */
function search_api_stats_views_data() {
  $data['search_api_stats']['table']['group'] = t('Search API stats');
  // For other base tables, explain how we join.
  $data['search_api_stats']['table']['join'] = [
    'users_field_data' => [
      'left_field' => 'uid',
      'field' => 'uid',
    ],
  ];

  $default_int = [
    'field' => [
      'id' => 'numeric',
      'click sortable' => TRUE,
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'handler' => 'argument',
    ],
    'sort' => [
      'handler' => 'sort',
    ],
  ];

  $default_string = [
    'field' => [
      'id' => 'standard',
      'click sortable' => TRUE,
    ],
    'filter' => [
      'id' => 'string',
    ],
    'argument' => [
      'handler' => 'argument',
    ],
    'sort' => [
      'handler' => 'sort',
    ],
  ];

  $default_language = [
    'field' => [
      'id' => 'standard',
      'click sortable' => TRUE,
    ],
    'filter' => [
      'id' => 'string',
    ],
    'argument' => [
      'handler' => 'argument',
    ],
  ];

  $default_date = [
    'field' => [
      'id' => 'date',
      'click sortable' => TRUE,
    ],
    'filter' => [
      'id' => 'date',
    ],
    'argument' => [
      'id' => 'date',
    ],
    'sort' => [
      'id' => 'date',
    ],
  ];

  // ----------------------------------------------------------------
  // search_api_stats table -- fields.
  $data['search_api_stats']['qid'] = [
    'title' => t('Primary Key'),
    'help' => t('The Search Query ID.'),
  ] + $default_int;

  $data['search_api_stats']['s_name'] = [
    'title' => t('Server name'),
    'help' => t('Server Name.'),
  ] + $default_string;

  $data['search_api_stats']['i_name'] = [
    'title' => t('Index machine name'),
    'help' => t('Index machine name'),
  ] + $default_string;

  $data['search_api_stats']['timestamp'] = [
    'title' => t('Timestamp'),
    'help' => t('Unix timestamp of when query occurred.'),
  ] + $default_date;

  $data['search_api_stats']['numfound'] = [
    'title' => t('Number of found results'),
    'help' => t('Number of results.'),
  ] + $default_int;

  $data['search_api_stats']['total_time'] = [
    'title' => t('Total time for the query'),
    'help' => t('Number of results.'),
  ] + $default_int;

  $data['search_api_stats']['prepare_time'] = [
    'title' => t('Prepare time'),
    'help' => t('Time taken by Search API prepare phase for this query (miliseconds).'),
  ] + $default_int;

  $data['search_api_stats']['process_time'] = [
    'title' => t('Process time'),
    'help' => t('Time taken by Search API process phase for this query (miliseconds).'),
  ] + $default_int;

  $data['search_api_stats']['uid'] = [
    'title' => t('User ID'),
    'help' => t('The {users}.uid of the user who triggered the query.'),
  ] + $default_int;

  $data['search_api_stats']['sid'] = [
    'title' => t('Session ID'),
    'help' => t('Session ID of user who triggered the query.'),
  ] + $default_string;

  $data['search_api_stats']['showed_suggestions'] = [
    'title' => t('Showed suggestions'),
    'help' => t('Indicates whether a spelling suggestion was shown.'),
  ] + $default_int;

  $data['search_api_stats']['page'] = [
    'title' => t('Page'),
    'help' => t('Current results page.'),
  ] + $default_string;

  $data['search_api_stats']['keywords'] = [
    'title' => t('Keywords'),
    'help' => t('Query keywords arguments.'),
  ] + $default_string;

  $data['search_api_stats']['filters'] = [
    'title' => t('Filters'),
    'help' => t('Query filter arguments.'),
  ] + $default_string;

  $data['search_api_stats']['sort'] = [
    'title' => t('Sort'),
    'help' => t('Query sort arguments.'),
  ] + $default_string;

  if (\Drupal::moduleHandler()->moduleExists('locale')) {
    $data['search_api_stats']['language'] = [
      'title' => t('Language'),
      'help' => t('Search Language In.'),
    ] + $default_language;
  }
  $data['search_api_stats']['table']['base'] = [
    'field' => 'qid',
    'title' => t('Search API stats'),
    'help' => t("Search API stats."),
    'weight' => -9,
  ];

  // Relationship to the 'Users' table.
  $data['search_api_stats']['uid'] = [
    'title' => t('User ID'),
    'help' => t('Relationship to users.'),
    'relationship' => [
      'label' => t('User ID'),
      'base' => 'users_field_data',
      'base field' => 'uid',
      // This allows us to not show this relationship if the base is already
      // user so users won't create circular relationships.
      'skip base' => ['users'],
    ],
  ];

  return $data;
}
