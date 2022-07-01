<?php declare(strict_types = 1);

namespace App\FrontModule\Presenters;

use K2D\News\Models\NewModel;
use Nette\Application\UI\Form;
use Nette\Mail\Message;
use Nette\Mail\SmtpException;
use Nette\Mail\SmtpMailer;
use Nette\Neon\Neon;

class HomepagePresenter extends BasePresenter
{

    /** @inject */
    public NewModel $newModel;

    public function renderDefault(): void
    {
        $this->template->news = $this->newModel->getPublicNews('cs')->limit(2);
    }

    public function renderCoaching(): void
    {
        // Render
    }

    public function renderEsences(): void
    {
        // Render
    }

    public function renderHomeopathy(): void
    {
        // Render
    }

    public function renderPainting(): void
    {
        // Render
    }

    public function createComponentContactForm(): Form
    {
        $form = new Form();

        $form->addText('name', 'Jméno a příjmení')
            ->addRule(Form::MAX_LENGTH, 'Maximální délka je %s znaků', 100)
            ->setRequired('Musíte zadat Vaše jméno a příjmení.');

        $form->addEmail('email', 'Email')
            ->addRule(Form::MAX_LENGTH, 'Maximální délka je %s znaků', 150)
            ->setRequired('Musíte zadat Váš email.');

        $form->addText('phone', 'Telefonní číslo')
            ->addRule(Form::MIN_LENGTH, 'Minimální délka je %s znaků', 9)
            ->addRule(Form::MAX_LENGTH, 'Maximální délka je %s znaků', 16)
            ->setRequired('Musíte zadat Váše telefonní číslo.');

        $form->addTextArea('message', 'Zpráva')
            ->addRule($form::MAX_LENGTH, 'Zpráva je příliš dlouhá', 5000)
            ->setRequired('Obsah zprávy nesmí zůstat prázdný.');

        $form->addSubmit('submit', 'Odeslat');

        $form->onSubmit[] = function (Form $form) {
            try {
                $values = $form->getValues();

                $mail = new Message();

                $vars = $this->configuration->getAllVars();
                if (isset($vars['email']))
                    $ownersEmail = $vars['email'];
                else
                    $ownersEmail = 'info@filipurban.cz';

                $mail->setFrom($values['email'], $values['name'])
                    ->addTo($ownersEmail)
                    ->setSubject('Zpráva z kontaktního formuláře (kralovajana.cz)')
                    ->setHtmlBody('<h4>Zpráva od: ' . $values['name'] . ' ('. $values['phone'] .')</h4>' . $values['message']);

                $parameters = Neon::decode(file_get_contents(__DIR__ . "/../../config/server/local.neon"));

                $mailer = new SmtpMailer([
                    'host' => $parameters['mail']['host'],
                    'username' => $parameters['mail']['username'],
                    'password' => $parameters['mail']['password'],
                    'secure' => $parameters['mail']['secure'],
                ]);

                $mailer->send($mail);

                $this->flashMessage('Email byl úspěšně odeslán!', 'success');

                if ($this->isAjax()) {
                    $this->redrawControl('contactFlashes');
                    $this->redrawControl('contactForm');
                    $form->setValues([], TRUE);
                } else {
                    $this->redirect('this#kontakt');
                }

            } catch (SmtpException $e) {
                $this->flashMessage('Vaši zprávu se nepodařilo odeslat. Kontaktujte prosím správce webu na info@filipurban.cz', 'danger');
            }
        };

        return $form;
    }

}
