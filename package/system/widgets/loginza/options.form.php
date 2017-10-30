<?php

class formWidgetLoginzaOptions extends cmsForm {

    public function init() {

        return array(

            array(
                'type' => 'fieldset',
                'title' => LANG_OPTIONS,
                'childs' => array(

                    new fieldText('options:provider', array(
                        'title' => LANG_WD_LOGINZA_PROVIDERS,
                        'rules' => array(
                            array('required')
                        ),
						'default' => 'vkontakte,facebook,mailruapi,google,yandex,openid,twitter,webmoney,rambler,flickr,mailru,loginza,myopenid,lastfm,verisign,aol,steam',
                    )),
					
					 new fieldList('options:group_id', array(
                        'title' => LANG_WD_LOGINZA_DEFAULT_GROUP,                        
                        'generator' => function($item) {
						
                            $groups_model = cmsCore::getModel('users');
                            $tree = $groups_model->getGroups();

                            $items = array();
                            
                            if ($tree) {
                                foreach ($tree as $item) {
                                    $items[$item['id']] = $item['title'];
                                }
                            }
                            return $items;
                        }
                    )),
				
                )
            ),

        );

    }

}
