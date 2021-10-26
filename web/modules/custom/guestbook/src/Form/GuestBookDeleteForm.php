<?php

namespace Drupal\guestbook\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;

/**
 * Сlass for deleting comments.
 */
class GuestBookDeleteForm extends ConfirmFormBase {

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return $this->t('delete_guest_comment');
  }

  /**
   * {@inheritDoc}
   */
  public $id;

  /**
   * {@inheritDoc}
   */
  public function getQuestion() {
    return $this->t('Delete comment');
  }

  /**
   * {@inheritDoc}
   */
  public function getCancelUrl() {
    return new Url('guestbook.reviews');
  }

  /**
   * {@inheritDoc}
   */
  public function getDescription() {
    return $this->t('Do this if you are sure you want it!');
  }

  /**
   * {@inheritDoc}
   */
  public function getConfirmText() {
    return $this->t('Delete it!');
  }

  /**
   * {@inheritDoc}
   */
  public function getCancelText() {
    return $this->t('Cancel');
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    $this->id = $id;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritDoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $query = \Drupal::database();
    $query->delete('guestbook')
      ->condition('id', $this->id)
      ->execute();
    $this->messenger()->addStatus(("This comment has been deleted"));
    $form_state->setRedirect('guestbook.reviews');
  }

}
