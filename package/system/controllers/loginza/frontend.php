<?php
class loginza extends cmsFrontend {
	
	public function actionError(){
		
		$form = cmsCore::getWidgetOptionsForm('loginza');

		exit('<h2>Для авторизации необходим E-mail</h2>');
	}
	
    public function actionAuth(){
		
        //if (!$this->request->isAjax()) { cmsCore::error404(); }
		
		$this->model->checkOpenId();
		
        $inCore = cmsCore::getInstance();
        $inDB   = cmsDatabase::getInstance();
        
        $token = $this->request->get('token','');
		$group_id = 3;

        $loginza_api_url = 'http://loginza.ru/api/authinfo';

        // получение профиля
        $profile = $this->model->loginzaRequest($loginza_api_url.'?token='.$token);
        
        $profile = json_decode($profile);
	
        // проверка на ошибки
        if (isset($profile->error_message) || isset($profile->error_type)) {
            exit('ERROR');
        }

        // ищем такого пользователя
        $user_id = $this->model->getUserByIdentity($profile->identity);
        
        // если пользователя нет, создаем
        if (!$user_id){
            $user_id = $this->model->createUser($profile, $group_id);
			if(!$user_id)
				header('Location: /loginza/error');
        }

        $auth_token = $this->model->getToken($user_id, $profile->identity);

        // если пользователь уже был или успешно создан, авторизуем
        if (!empty($auth_token))
            cmsUser::autoLogin($auth_token);
		
		header('Location: /');
	}

}

