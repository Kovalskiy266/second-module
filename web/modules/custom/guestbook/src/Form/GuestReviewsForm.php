<?php

namespace Drupal\guestbook\Form;

use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\RedirectCommand;

/**
 * Form for sending comments.
 */
class GuestReviewsForm extends FormBase {

  /**
   * {@inheritDoc}
   */
  public function getFormId(): string {
    return 'guestbook_form';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attached'] = ['library' => ['guestbook/guestbook.reviews']];
    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t("Your email"),
      "#placeholder" => $this->t('Enter your email'),
      "#required" => TRUE,
      '#pattern' => '^\S+@\S+\.\S+$',
      '#attributes' => [
        'title' => $this->t("The name can only contain latin letters, an underscore, or a hyphen"),
        'class' => ['custom-class'],
      ],
      '#ajax' => [
        'disable-refocus' => TRUE,
        'event' => 'finishedinput',
        "callback" => "::validateEmail",
        'progress' => [
          'type' => 'none',
        ],
      ],
      '#suffix' => '<div class="email-validation-message"></div>',
    ];

    $form['phone'] = [
      '#type' => 'tel',
      '#pattern' => '^[0-9]{9,15}$',
      '#required' => TRUE,
      '#title' => $this->t('Your phone number'),
      "#placeholder" => $this->t('Enter your phone number'),
      '#attributes' => [
        'title' => $this->t("The phone number can only numbers"),
        'class' => ['custom-class'],
      ],
      '#ajax' => [
        'disable-refocus' => TRUE,
        'callback' => '::validateNumberPhone',
        'event' => 'finishedinput',
        'progress' => [
          'type' => 'none',
        ],
      ],
      '#suffix' => '<div class="phone-validation-message"></div>',
    ];

    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t("Your name:"),
      '#placeholder' => $this->t("Enter the name"),
      "#required" => TRUE,
      '#pattern' => '^[aA-zZ]{2,100}$',
      '#attributes' => [
        'title' => $this->t("Minimum length of the name is 2 characters, and the maximum is 100"),
        'class' => ['custom-class'],
      ],
      '#ajax' => [
        'disable-refocus' => TRUE,
        'callback' => '::validateName',
        'event' => 'finishedinput',
        'progress' => [
          'type' => 'none',
        ],
      ],
      '#suffix' => '<div class="name-validation-message"></div>',
    ];

    $form['review'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Your review:'),
      '#placeholder' => $this->t("Enter the review:"),
      "#required" => TRUE,
      '#attributes' => [
        'class' => ['custom-class'],
      ],
    ];

    $form['avatar'] = [
      '#type' => "managed_file",
      '#title' => $this->t('Avatar of user'),
      '#description' => $this->t('Select file with extension jpg, jpeg or png'),
      '#upload_location' => 'public://images/avatar/',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [2097152],
      ],
    ];

    $form['image'] = [
      '#type' => "managed_file",
      '#title' => $this->t('Image'),
      '#description' => $this->t('Select file with extension jpg, jpeg or png'),
      '#upload_location' => 'public://images/pictures/',
      '#upload_validators' => [
        'file_validate_extensions' => ['png jpg jpeg'],
        'file_validate_size' => [5097152],
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#name' => 'submit',
      '#value' => $this->t("Add comment"),
      '#ajax' => [
        'callback' => '::setMessage',
        'progress' => [
          'type' => 'none',
        ],
      ],
    ];

    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<div id="result_message"></div>',
    ];

    return $form;
  }

  /**
   * When sending, we insert our data into the database.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::messenger()->addStatus($this->t('Succes'));
    $database = \Drupal::database();
    $avatar = $form_state->getValue('avatar');
    $image = $form_state->getValue('image');
    $file = File::load($image[0]);
    $avatar_file = File::load($avatar[0]);
    // If the photo has been uploaded, save it.
    if ($avatar != NULL || $image != NULL) {
      $file->setPermanent();
      $file->save();
      $avatar_file->setPermanent();
      $avatar_file->save();
    }

    // We insert our data into the database table.
    $database->insert('guestbook')
      ->fields([
        'name' => $form_state->getValue('name'),
        'email' => $form_state->getValue('email'),
        'phone' => $form_state->getValue('phone'),
        'review' => $form_state->getValue('review'),
        'avatar' => $form_state->getValue('avatar')[0],
        'image' => $form_state->getValue('image')[0],
        'date_created' => time(),
      ])
      ->execute();
  }

  /**
   * {@inheritDoc}
   */
  /**
   * We validate the phone number through
   * a regular expression, using the preg_match function.
   */

  public function validateNumberPhone(array &$form, FormStateInterface $form_state): object {
    $telephone = $form_state->getValue('phone');
    $response = new AjaxResponse();
    if (!preg_match('/^[0-9]{9,15}$/', $telephone)) {
      $response->addCommand(
        new HtmlCommand(
          '.phone-validation-message',
          '<div class = "invalid-message-phone>">' . $this->t('The number must contain only numbers!')
        ),
      );
      $response->addCommand(
        new CssCommand(
          '.form-tel',
          ['border-color' => 'red']
        )
      );
    }
    else {
      $response->addCommand(
        new HtmlCommand(
          '.phone-validation-message',
          ''
        ),
      );
      $response->addCommand(
        new CssCommand(
          '.form-tel',
          ['border-color' => 'green']
        )
      );
    }
    return $response;
  }

  /**
   * We validate the name through a regular expression,
   * using the preg_match function.
   */
  public function validateName(array &$form, FormStateInterface $form_state): object {
    $response = new AjaxResponse();
    if (!preg_match('/^[aA-zZ]{2,100}$/', $form_state->getValue('name'))) {
      $response->addCommand(
        new HtmlCommand(
          '.name-validation-message',
          '<div class = "invalid-message-name>">' . $this->t('The name must contain between 2 and 100 Latin characters!')
        ),
      );
      $response->addCommand(
        new CssCommand(
          '.form-text',
          ['border-color' => 'red']
        )
      );
    }
    else {
      $response->addCommand(
        new HtmlCommand(
          '.name-validation-message',
          ''
        ),
      );
      $response->addCommand(
        new CssCommand(
          '.form-text',
          ['border-color' => 'green']
        )
      );
    }
    return $response;
  }

  /**
   * Ajax submitting.
   */
  public function setMessage(array &$form, FormStateInterface $form_state): object {
    $response = new AjaxResponse();
    if ($form_state->hasAnyErrors()) {
      $response->addCommand(
        new HtmlCommand(
          '#result_message',
          '<div class="cat-message">' . $this->t('There are mistakes in your form! Check the fields for correct input!')
        ),
      );
    }
    else {
      $response->addCommand(
        new HtmlCommand(
          '#result_message',
          ''
        )
      );
      $response->addCommand(new RedirectCommand('\guestbook\reviews'));
      $response->addCommand(new InvokeCommand('.custom-class', 'val', ['']));
    }
    return $response;
  }

  /**
   * We validate the email through a regular expression,
   * using the preg_match function.
   */
  public function validateEmail(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $email = $form_state->getValue('email');
    if (!preg_match('/^\S+@\S+\.\S+$/', $email)) {
      $response->addCommand(
        new HtmlCommand(
          '.email-validation-message',
          '<div class = "invalid-email-message">' . $this->t('The characters you entered are invalid in the email, enter the correct email!') . '</div>'
        )
      );
      $response->addCommand(
        new CssCommand(
          '.form-email',
          ['border-color' => 'red']
        )
      );
    }
    else {
      $response->addCommand(
        new HtmlCommand(
          '.email-validation-message',
          ''
        )
      );
      $response->addCommand(
        new CssCommand(
          '.form-email',
          ['border-color' => 'green']
        )
      );
    }
    return $response;
  }

}
