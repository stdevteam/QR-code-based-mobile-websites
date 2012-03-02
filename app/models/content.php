<?php
	class Content extends AppModel{ 
		var $name = 'Content';
                
                function findArticlesForPage($id){
                    $sql = "SELECT * FROM articles WHERE `page_id` = ".$id." AND `parent_id` = '0' ";
                    $result = $this->query($sql);
                    if(!empty($result)){
				return $result;
                        }else{
				return false;
			}
                }
                
                function findArticlesForParent($id){
                    $sql = "SELECT * FROM articles WHERE `parent_id` = ".$id;
                    $result = $this->query($sql);
                    if(!empty($result)){
				return $result;
                        }else{
				return false;
			}
                }
                
                function findArticleById($id){
                    $sql = "SELECT * FROM articles WHERE `id` = ".$id;
                    $result = $this->query($sql);
                    if(!empty($result)){
				return $result;
                        }else{
				return false;
			}
                        
                }
                
                function saveArticle($data){
                    $sql = "UPDATE articles SET `text` = '".$data['text']."', `name` = '".$data['name']."' WHERE id = ".$data['id'];
                    $result = $this->query($sql);
                    if(!empty($result)){
				return $result;
                        }else{
				return false;
			}
                }
                
                function addArticle($data){
                    $sql = "INSERT INTO articles (name,text,page_id,parent_id) VALUES('{$data['name']}','{$data['text']}','{$data['page_id']}','{$data['parent_id']}') ";
                    $result = $this->query($sql);
                    if(!empty($result)){
				return $result;
                        }else{
				return false;
			}
                }
                function findPageId($name){
                    //$fields = array('fields' => array('Content.id'));
                    $conditions = array('Content.type' => 'pages',
                                        'Content.status' => 'published',
                                        'Content.title' => $name,
                                        
                        );
                    $result = $this->find('first', array('conditions' => $conditions, 'fields' => 'Content.id'));
                    if(!empty($result)){
				return $result;
                        }else{
				return false;
			}
                }
                
                function hasChild($id){
                    $sql = "SELECT * FROM articles WHERE `parent_id` = ".$id;
                    $result = $this->query($sql);
                    if(count($result) > 0){
				return true;
                        }else{
				return false;
			}
                }
                
                public function sliderItems(){
                    $sql = "SELECT contents.image,contents.slider_text,contents.text,contents.badge,contents.listing_id,places.title FROM contents  
                        LEFT JOIN places ON places.id = contents.listing_id 
                        WHERE contents.type = 'slider' 
                        AND contents.status = 'published' 
                        AND places.approved = 'yes' 
                        ORDER BY contents.order ASC 
                        LIMIT 5";
                    $sliderItems = $this->query($sql);
                    return $sliderItems;
                }
                
	}
?>