<?php
class modelLoginza extends cmsModel {

    public function getToken($user_id, $token)
    {
        $users_model = cmsCore::getModel('users');
        
        $auth_token = md5($token);
        
        $users_model->deleteAuthToken($auth_token);
        
        $users_model->setAuthToken($user_id, $auth_token);
        
        return $auth_token;
    }

    public function checkOpenId(){

        $inDB = cmsDatabase::getInstance();

        if (!$inDB->isFieldExists('users', 'openid')){
            $inDB->query("ALTER TABLE `cms_users` ADD `openid` VARCHAR( 250 ) NULL, ADD INDEX ( `openid` )");
        }
		
		return true;
    }
	
	public function getUserByIdentity($identity){
        $inDB   = cmsDatabase::getInstance();
		$user_id = $inDB->getField('users', "openid='{$identity}'", 'id');
		if(!$user_id)
			return false;
		else
			return $user_id;
    }
	
	public function loginzaRequest($url) {

        if (function_exists('curl_init')){

            $curl = curl_init($url);
            $user_agent = 'Loginza-API/InstantCMS';

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);
            curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $raw_data = curl_exec($curl);
            curl_close($curl);

            return $raw_data;

        } else {

            return file_get_contents($url);

        }

    }
	
	public function createUser($profile,$group_id){

        $inCore = cmsCore::getInstance();
        $inDB   = cmsDatabase::getInstance();

        $nickname = '';

        if (isset($profile->name->full_name)){
            if($profile->name->full_name){
                // указано полное имя
                $nickname   = $profile->name->full_name;
            }
        } 
        
        if (!$nickname){
            if(isset($profile->name->first_name)) {
                if ($profile->name->first_name){
                    // указано имя и фамилия по-отдельности
                    $nickname  = $profile->name->first_name;
                    if (isset($profile->name->last_name)){
                        if ($profile->name->last_name){ 
                            $nickname .= ' '. $profile->name->last_name;
                        }
                    }
                }
            }
        }

        if (!$nickname){
            if(preg_match('/^(http:\/\/)([a-zA-Z0-9\-_]+)\.([a-zA-Z0-9\-_]+)\.([a-zA-Z]{2,6})([\/]?)$/i', $profile->identity)) {
                // не указано имя, но передан идентификатор в виде домена 3-го уровня
                $nickname = str_replace('http://', '', $profile->identity);
                $nickname = substr($nickname, 0, strpos($nickname, '.'));
            }
        }
		
		$password		= substr(md5(substr(md5(time().$profile->identity.$_SERVER['HTTP_HOST']),0,6)),6);
		$password_salt 	= time();
        
        if($profile->photo){
            //Загружаем аватар
        }
        
        //Email
        if(!empty($profile->email)){
        	//Поиск дубликата
        	$dublicate_email = $inDB->getField('users', "email = '{$profile->email}'", 'email');
     		//Если нет такого емайла, присваиваем его в профиль.
			if (!$dublicate_email) { 
			    $email = $profile->email; 
			}else{
				$email = substr(md5($profile->identity),0,6).'@'.$_SERVER['HTTP_HOST'];
			}
        }else{ 
		    $email = substr(md5($profile->identity),0,6).'@'.$_SERVER['HTTP_HOST'];
		}

        $birthdate = null;

        if ($profile->dob)
        {
            $time = strtotime($profile->dob);
        
            if (1970 < date('Y', $time))
            {
                $birthdate = date('Y-m-d', $time);
            }
        }
		
		$user = array(
			'password' => md5(md5($password) . $password_salt),
			'password_salt' => $password_salt,
			'nickname'=> $nickname,
			'groups' => '---' . PHP_EOL . '- '.$group_id,
			'email' => $email,
			'birth_date' => $birthdate,
			'openid' => $profile->identity,
			'date_reg' => date('Y-m-d')
		);

        $user_id = $this->insert('{users}', $user);
	
		if($user_id){
			return $user_id;
		}

        return false;
    }

}
