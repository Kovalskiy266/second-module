<?php

namespace Drupal\guestbook\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\RedirectCommand;

/**
 * Class for editing comments.
 */
class GuestBookEditForm extends GuestReviewsForm {

  /**
   * {@inheritDoc}
   */
  public function getFormId(): string {
    return $this->t('edit_guestbook_form');
  }

  /**
   * {@inheritDoc}
   */
  protected $comment;

  /**
   * {@inheritDoc}
   */

  /**
   * By inheriting the basic form class,
   * we get the default values of our parent form.
   */
  public function buildForm(array $form, FormStateInterface $form_state, int $id = NULL) : array {
    $form = parent::buildForm($form, $form_state);
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
      ])
      ->condition('id', $id)
      ->execute()
      ->fetch();
    $this->comment = $result;
    $form['name']['#default_value'] = $result->name;
    $form['email']['#default_value'] = $result->email;
    $form['phone']['#default_value'] = $result->phone;
    $form['review']['#default_value'] = $result->review;
    $form['avatar']['#default_value'][] = $result->avatar;
    $form['image']['#default_value'][] = $result->image;
    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<div id="result_message"></div>',
    ];
    $form['submit']['#value'] = $this->t('Edit comment');
    return $form;
  }

  /**
   * {@inheritDoc}
   */
  public function validateName(array &$form, FormStateInterface $form_state): object {
    return parent::validateName($form, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  public function validateEmail(array &$form, FormStateInterface $form_state) : object {
    return parent::validateEmail($form, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  public function validateNumberPhone(array &$form, FormStateInterface $form_state):object {
    return parent::validateNumberPhone($form, $form_state);
  }

  /**
   * When we submit this form, we change our values.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $database = \Drupal::database();
    $name = $form_state->getValue('name');
    $email = $form_state->getValue('email');
    $phone = $form_state->getValue('phone');
    $review = $form_state->getValue('review');
    $avatar = $form_state->getValue('avatar')[0];
    $image = $form_state->getValue('image')[0];
    $database
      ->update('guestbook')
      ->condition('id', $this->comment->id)
      ->fields(
        [
          'name' => $name,
          'email' => $email,
          'phone' => $phone,
          'review' => $review,
          'avatar' => $avatar,
          'image' => $image,
        ],
      )
      ->execute();

    // If we upload a new photo, we delete the old one from the files.
    if ($image != $this->comment->image) {
      File::load($this->comment->image)->delete();
    }
  }

  /**
   * {@inheritDoc}
   */
  public function setMessage(array &$form, FormStateInterface $form_state): object {
    $response = parent::setMessage($form, $form_state);
    // If we do not receive any errors after.
    // editing our form, we close our dialog window.
    if (!$form_state->hasAnyErrors()) {
      $response->addCommand(new CloseModalDialogCommand());
    }
    $response->addCommand(new RedirectCommand('\guestbook\reviews'));
    return $response;
  }

}
