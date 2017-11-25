<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\Task */
/* @var $domains array */
/* @var $domainsCount integer */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Tasks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="task-view">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php if ($domainsCount > 0): ?>
        <p>Successfully pushed <?= $domainsCount ?> domain's to RabbitMQ</p>
    <?php endif; ?>
    <p>
        <?= Html::a('Push to queue', ['push', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'id_parent_task',
            'creation_date',
            'filename',
        ],
    ]) ?>
    <h2>Domains list</h2>
    <ul>
        <?php foreach ($domains as $domain): ?>
        <li><?= $domain ?></li>           
        <?php endforeach; ?>
    </ul>
</div>
