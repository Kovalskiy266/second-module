<?php

namespace Drupal\guestbook\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;

/**
 * Returns responses for guestbook routes.
 */
class GuestReviewsController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function build(): array {
    $form = \Drupal::formBuilder()->getForm('Drupal\guestbook\Form\GuestReviewsForm');
    $guestbook_table = $this->guestBookTable();
    return [
      '#theme' => 'guestbook-page',
      '#form' => $form,
      '#guestbook_table' => $guestbook_table,
    ];
  }

  /**
   * We extract values from the database and pass them in an array.
   */
  public function guestBookTable(): array {
    $database = \Drupal::database();
    $result = $database->select('guestbook', 'g')
      ->fields('g', [
        'id',
        'name',
        'email',
        'phone',
        'review',
        'avatar',
        'image',
        'date_created',
      ]);
    $query = $result->execute()->fetchAll();
    $rows = [];
    foreach ($query as $value) {
      if ($value->avatar != NULL) {
        $value->avatar = [
          '#theme' => 'image_style',
          '#style_name' => 'medium',
          '#uri' => File::load($value->avatar)->getFileUri(),
          '#attributes' => [
            'class' => 'avatar-image',
            'alt' => 'avatar of user',
            'width' => 100,
            'height' => 100,
          ],
        ];
      }
      else {
        $value->avatar = [
          '#theme' => 'image',
          '#style_name' => 'medium',
          '#uri' => '/modules/custom/guestbook/files/user-without-avatar.png',
          '#attributes' => [
            'class' => 'avatar-imagae',
            'alt' => 'avatarsd of user',
            'width' => 100,
            'height' => 100,
          ],
        ];
      }

      if ($value->image != NULL) {
        $value->image = [
          '#theme' => 'image_style',
          '#style_name' => 'medium',
          '#uri' => File::load($value->image)->getFileUri(),
          '#attributes' => [
            'class' => 'image-for-comment',
            'alt' => 'Image',
            'width' => 200,
            'height' => 200,
          ],
        ];
      }

      $rows[] = [
        'id' => $value->id,
        'name' => $value->name,
        'email' => $value->email,
        'phone' => $value->phone,
        'review' => $value->review,
        'avatar' => ['data' => $value->avatar],
        'image'  => ['data' => $value->image],
        'date_created' => date('Y-m-d H:i:s', $value->date_created),
      ];
    }
    if ($rows != NULL) {
      krsort($rows);
    }
    return $rows;
  }

}
