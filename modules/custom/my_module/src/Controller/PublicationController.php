<?php

namespace Drupal\my_module\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;

/**
 * Provides a controller for the publication list page.
 */
class PublicationController extends ControllerBase {

  /**
   * Returns a page listing all publications.
   *
   * @return array
   *   A render array representing the publication list page.
   */
  public function publicationList() {
    $header = [
      $this->t('Publication ID'),
      $this->t('Title'),
      $this->t('Corresponding Authors'),
    ];
    $rows = [];

    $node_storage = $this->entityTypeManager()->getStorage('node');
    $nids = $node_storage->getQuery()
      ->condition('type', 'publication')
      ->sort('field_publication_id', 'ASC')
      ->execute();
    $nodes = $node_storage->loadMultiple($nids);

    foreach ($nodes as $node) {
      $id = $node->id();
      $title = $node->getTitle();
      $corresponding_authors = [];
      foreach ($node->get('field_corresponding_authors') as $author) {
        $corresponding_authors[] = $author->entity->toLink();
      }
      $title_url = [
        'data' => [
          '#type' => 'link',
          '#title' => $title,
          '#url' => Url::fromRoute('entity.node.canonical', ['node' => $node->id()]),
	],
      ];
      $rows[] = [
        $id,
        $title_url,
        [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% for author in authors %}{{ author }}{% if not loop.last %}, {% endif %}{% endfor %}',
            '#context' => [
              'authors' => $corresponding_authors,
            ],
          ],
        ],
      ];
    }

    $build = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => $this->t('No publications found.'),
    ];

    return $build;
  }

}

