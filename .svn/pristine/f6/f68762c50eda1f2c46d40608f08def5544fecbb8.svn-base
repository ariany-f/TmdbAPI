<?php
namespace App\Common;

use App\Controller\Component\UtilsComponent;
use Cake\Mailer\Email;
use Cake\Validation\Validator;
use Cake\Core\Configure;

/**
 * Mail Component
 *
 * Example of use:
 *  $this->Mail->setOptions($opts)->send();
 *
 */
class Mailer
{
    /**
     * Email Options
     * @var array
     */
    private $opts;

    /**
     * Validation Errors
     * @var array|string
     */
    private $validationErrors;

    /**
     * @param array $opts (settings, layout, template, vars, to, subject)
     * @return Mailer
     */
    public function setOpts(array $opts): self
    {
        $defaults = [
            'settings' => Configure::read('debug') ? 'debug' : 'default',
            'layout' => 'default',
            'template' => 'default',
            'vars' => [],
        ];
        $this->opts = array_merge($defaults, $opts);
        return $this;
    }

    /**
     * Reset Mailer
     * Reset validationErrors and Class properties
     */
    public function reset()
    {
        $this->validationErrors = null;
    }

    /**
     * Get validation errors
     * @return array|string
     */
    public function getErrors()
    {
        return $this->validationErrors;
    }

    /**
     * Get validation errors
     * @param null $errors
     */
    public function setErrors($errors = null)
    {
        $this->validationErrors = $errors;
    }

    /**
     * Send Email
     * @return bool
     * @throws \Exception
     */
    public function send()
    {
        $this->reset();
        if (!$this->opts)
        {
            throw new \Exception('Email params not set');
        }

        if (!$this->validate())
        {
            return false;
        }

        try {
            if(!isset($this->opts['bcc']))
            {
                $this->opts['bcc'] = Configure::read('mails.debug');
            }
            if (! is_array($this->opts['to']))
            {
                $this->opts['to'] = [$this->opts['to']];
            }
            if (! is_array($this->opts['bcc']))
            {
                $this->opts['bcc'] = [$this->opts['bcc']];
            }

            $email = new Email($this->opts['settings']);
            $email
                ->setTemplate($this->opts['template'])
                ->setLayout($this->opts['layout'])
                ->SetTo($this->opts['to'])
                ->setBcc($this->opts['bcc'])
                ->setSubject($this->opts['subject'])
                ->setEmailFormat('html')
                ->setViewVars($this->opts['vars'])
                ->setHelpers([
                    'Utils'
                ]);

            if(isset($this->opts['attachments']))
            {
                $email->setAttachments($this->opts['attachments']);
            }
            $email->send();
        }
        catch (\Exception $e)
        {
            UtilsComponent::pr($e, 1);
            $this->setErrors($e->getMessage());
            return false;
        }
        sleep(1);
        return true;
    }

    /**
     * Validate email params
     * @return bool
     */
    private function validate()
    {
        $validator = new Validator();
        $validator
            ->requirePresence('subject')
            ->requirePresence('to');

        $this->setErrors($validator->errors($this->opts));
        return empty($this->validationErrors);
    }
}