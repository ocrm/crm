<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */

$this->title = $title;
$this->params['breadcrumbs'][] = [
    'label' => 'Записная книжка',
    'url' => Url::to(['/helper/notebook/index']),
];
if($action == "update"){
    $this->params['breadcrumbs'][] = [
        'label' => $model->company,
        'url' => Url::to(['/helper/notebook/view', 'id' => $model->id]),
    ];
}
$this->params['breadcrumbs'][] = $this->title;
?>

<?
$form = ActiveForm::begin([
    'id' => 'company-form',
    'options' =>[
        'enctype'=>'multipart/form-data'
    ]
])
?>
<div class="panel panel-default">
    <div class="panel-body">
        <div class="col-lg-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4>Карточка предприятия</h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <?= $form->field($model, 'type')->dropDownList($model->typeArr) ?>
                        </div>
                        <div class="col-lg-4">
                            <?= $form->field($model, 'cameFrom')->dropDownList($model->cameFromArr) ?>
                        </div>
                        <div class="col-lg-4">
                            <?= $form->field($model, 'status')->dropDownList($model->statusArr) ?>
                        </div>
                    </div>
                    <?= $form->field($model, 'company') ?>
                    <?= $form->field($model, 'city') ?>
                    <?= $form->field($model, 'ready')->radioList($model->readyArr,
                        [
                            'item' => function($index, $label, $name, $checked, $value) {
                                    ($checked) ? $checked = 'checked' : $checked;
                                    $return = '<input id="ready-radio'.$value.'" type="radio" name="' . $name . '" value="' . $value . '"'.$checked.'>';
                                    $return .= '<label for="ready-radio'.$value.'" style="background-color: '. $label[0] .'">'.$label[1].'</label>';
                                    return $return;
                                },
                            'class' => 'clearfix'
                        ]
                    ) ?>
                    <div class="row">
                        <div class="col-lg-4">
                            <?= $form->field($model, 'callBack')->input('date', ['class' => 'datepicker form-control']) ?>
                        </div>
                        <? if($action == 'update'): ?>
                            <div class="col-lg-4">
                                    <?= $form->field($kp, 'name')?>
                            </div>
                            <div class="col-lg-4">
                                <?= $form->field($kp, 'file')->fileInput() ?>
                            </div>
                        <? endif ?>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <table class="table">
                                <? foreach ($model->kp as $kp): ?>
                                    <tr>
                                        <td>
                                            <?= Html::a($kp->name, 'uploads/kp/'.$kp->file, ['target' => '_blank']) ?>
                                        </td>
                                        <td>
                                            <?= $kp->date ?>
                                        </td>
                                        <td>
                                            <?= Html::a('<i class=" glyphicon glyphicon-remove"></i>', Url::to(['helper/notebook/remove-file', 'id' => $kp->id]), ['class' => 'order-file-delete confirm-msg pull-right']) ?>
                                        </td>
                                    </tr>
                                <? endforeach ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel panel-default">
                <div class="panel-body">
                    <?= $form->field($model, 'comment')->textarea(['class' => 'tinymce']) ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4>Контактное лицо</h4>
                </div>
                <div class="panel-body">
                    <?= $form->field($model, 'position') ?>
                    <?= $form->field($model, 'firstName') ?>
                    <?= $form->field($model, 'secondName') ?>
                    <?= $form->field($model, 'thirdName') ?>
                    <?= $form->field($model, 'email') ?>
                    <?= $form->field($model, 'phone') ?>
                </div>
            </div>
            <? if(Yii::$app->user->identity->access > 50): ?>
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4>Менеджер</h4>
                    </div>
                    <div class="panel-body">
                        <?= $form->field($model, 'managerId')->dropDownList($managers) ?>
                    </div>
                </div>
            <? endif ?>
        </div>
        <div class="form-group">
            <div class="col-lg-12">
                <?= Html::submitButton('Сохранить', ['class' => 'btn btn-primary pull-right']) ?>
            </div>
        </div>
    </div>
</div>
<?php ActiveForm::end() ?>

