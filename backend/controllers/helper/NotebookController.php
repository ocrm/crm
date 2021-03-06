<?php
namespace backend\controllers\helper;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use backend\models\helper\notebook\Notebook;
use backend\models\helper\notebook\History;
use backend\models\helper\notebook\SearchForm;
use backend\models\settings\Managers;
use yii\helpers\ArrayHelper;
use yii\data\Sort;
use yii\web\ForbiddenHttpException;
use backend\models\Model;
use yii\web\UploadedFile;
use backend\models\helper\notebook\Kp;

/**
 * Site controller
 */
class NotebookController extends Controller
{
    /**
     * @inheritdoc
     */

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['index', 'view', 'new', 'update', 'remove', 'create', 'delete', 'search', 'remove-file'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new Notebook();
        $dataProvider = $searchModel->searchModel(Yii::$app->request->get());
        $search = new SearchForm();
        return $this->render('index',[
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'search' => $search,
            'managers' => ArrayHelper::index(Managers::find()->asArray()->all(), 'id'),
        ]);
    }

    public function actionSearch($text){
        $model = History::find()->where(['LIKE', 'text', $text])->all();
        return $this->render('search',[
            'model' => $model,
            'notebook' => ArrayHelper::index(Notebook::find()->asArray()->all(), 'id'),
            'managers' => ArrayHelper::index(Managers::find()->asArray()->all(), 'id'),
        ]);
    }

    public function actionView($id)
    {

    }

    public function actionNew()
    {
        $model = new Notebook();
        if($model->load(Yii::$app->request->post()) && $model->validate()){

            //Администратор сам привязывает менеджеров Заменить на RBAC
            if(Yii::$app->user->identity->access < 100){
                $model->managerId = Yii::$app->user->identity->managerId;
            }
            $model->date = date('Y-m-d');
            $model->save();

            return $this->redirect(['/helper/notebook/index']);
        }

        return $this->render('form',[
            'model' => $model,
            'action' => 'create',
            'title' => 'Создать',
            'managers' => ArrayHelper::map(Managers::find()->all(),'id','fullName'),
        ]);
    }

    public function actionUpdate($id)
    {
        //Модель истории общения с клиентом
        $historyModel = new History();
        $history = History::find()->where(['notebookId' => $id])->orderBy(['date' => SORT_DESC, 'time' => SORT_DESC])->all();
        if($historyModel->load(Yii::$app->request->post()) && $historyModel->validate()){
            $historyModel->notebookId = $id;
            $historyModel->date = date('Y-m-d');
            $historyModel->time = date('H:i:s');
            $historyModel->save();
            Yii::$app->getSession()->setFlash('success', 'Запись добавлена');
            return $this->redirect(['/helper/notebook/update','id' => $id]);
        }

        //Модель карточки клиента
        $model = Notebook::findOne($id);
        $kp = new Kp();
        if(Yii::$app->user->identity->access < 100){
            if($model->managerId != Yii::$app->user->identity->managerId){
                throw new ForbiddenHttpException('Доступ запрещен');
            }
        }

        if($model->load(Yii::$app->request->post()) && $kp->load(Yii::$app->request->post()) && $model->validate() && $kp->validate()){

            $model->save();
            if(UploadedFile::getInstance($kp, 'file')){
                $kp->file = Model::uploadFile('kp/', 'Kp[file]', uniqid(Yii::$app->user->identity->managerId.'-kp-'), $kp->file);
                $kp->name = (!$kp->name) ?  $kp->file : $kp->name;
                $kp->companyId = $model->id;
                $kp->managerId = Yii::$app->user->identity->managerId;
                $kp->date = date("Y-m-d");
                $kp->save();
            }
            Yii::$app->getSession()->setFlash('success', 'Изменения сохранены');
            return $this->redirect(['/helper/notebook/update','id' => $id]);
        }

        return $this->render('form',[
            'model' => $model,
            'kp' => $kp,
            'historyModel' =>$historyModel,
            'history' => $history,
            'managers' => ArrayHelper::map(Managers::find()->all(),'id','fullName'),
            'action' => 'update',
            'title' => 'Редактировать',
            'id' => $id,
        ]);
    }

    //POST ACTION//
    ///////////////
    public function actionDelete($id)
    {
        $model = Notebook::findOne($id);

        if(Yii::$app->user->identity->access < 100){
            if($model->managerId != Yii::$app->user->identity->managerId){
                throw new ForbiddenHttpException('Доступ запрещен');
            }
        }
        foreach ($model->kp as $data){
            if(file_exists('uploads/kp/'.$data->file)){
                unlink('uploads/kp/'.$data->file);
            }
        }
        $model->delete();
        return $this->redirect(['/helper/notebook/index']);
    }

    public function actionRemoveFile($id)
    {
        $model = Kp::findOne($id);

        if(Yii::$app->user->identity->access < 100){
            if($model->managerId != Yii::$app->user->identity->managerId){
                throw new ForbiddenHttpException('Доступ запрещен');
            }
        }

        if(file_exists('uploads/kp/'.$model->file)){
            unlink('uploads/kp/'.$model->file);
        }

        $model->delete();
        Yii::$app->getSession()->setFlash('success', 'Файл удален');
        return $this->redirect(['/helper/notebook/update', 'id' => $model->companyId]);
    }
}
