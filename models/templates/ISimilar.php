<?php
namespace app\models\templates;


interface ISimilar
{
    /**
     * Сравнение шаблонов друг с другом на предмет похожести
     * @param app\models\PageSource $template
     * @return boolean результат проверки
     */
    public function looksLike($template);
}
