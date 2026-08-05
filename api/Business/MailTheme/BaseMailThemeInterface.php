<?php 
namespace Business\MailTheme;

interface BaseMailThemeInterface
{
    /**
     * @param string $template
     * @return string
     */
    public function wrapTemplate(string $template);
}