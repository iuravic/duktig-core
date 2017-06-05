<?php
namespace Duktig\Core\View;

interface RendererInterface
{
    /**
     * Renders the HTML by using a template.
     * 
     * @param string $template
     * @param array $data [optional]
     * @return string $html
     */
    public function render(string $template, array $data = []) : string;
}