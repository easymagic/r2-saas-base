<?php 
namespace Application\MailNotifications\Base;

interface BaseMailThemeInterface
{
    /**
     * @param string $template
     * @return string
     */
    public function wrapTemplate(string $template);
}