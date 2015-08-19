<?php
 	require_once("Rest.inc.php");
 	require_once("Db_config.php");
	
	class API extends DB_CONFIG {
	
		protected $data = "";

		protected $db = NULL;
		protected $pdo = NULL;
		public function __construct(){
			parent::__construct();				// Init parent contructor
			$siteName = $this->_request['site_name'];
			$this->setDBconfig($siteName);					// Initiate Database connection
		}

		/*
		 *  Generate uuid
		*/
		private function generate_uuid() {
			return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
				mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
				mt_rand( 0, 0xffff ),
				mt_rand( 0, 0x0fff ) | 0x4000,
				mt_rand( 0, 0x3fff ) | 0x8000,
				mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
			);
		}
		
		
		/*
		 * Dynmically call the method based on the query string
		 */
		public function processApi(){
			$func = strtolower(trim(str_replace("/","",$_REQUEST['x'])));
			if((int)method_exists($this,$func) > 0)
				$this->$func();
			else
				$this->response($this->json(array('status'=>'Fail','msg'=>'Method not found')),200); // If the method not exist with in this class "Page not found".
		}

		private function login(){;
			$username = $this->_request['username'];
			$password = $this->_request['password'];

			if(!empty($username) && !empty($password)){
				$login = $this->pdo->prepare('
					SELECT
						uuid
						,username
						,password
					FROM
						users
					WHERE
						username = :username
					LIMIT 1
				');
				$login->execute(array(
					':username' => $username
				));
				$result = $login->fetch(PDO::FETCH_ASSOC);
				if (count($result)) {
					$savedPassword = $result['password'];
					if(password_verify($password,$savedPassword)){
						// Passwords match so add a token to db
						$token = $this->generate_uuid();
						$userUUID = $result['uuid'];
						$updateToken = $this->pdo->prepare('
							UPDATE
								users
							SET
								token = :token
							WHERE
								uuid = :uuid
						');
						$updateToken->execute(array(
							':token'=>$token,
							':uuid'=>$userUUID
						));
						$result['token'] = $token;
						$this->response($this->json(array('token'=>$result['token'],'uuidUser'=>$result['uuid'])), 200);
					} else {
						$this->response($this->json(array('status'=>'Fail','msg'=>'Incorrect username/password')), 200);
					}
				}
			}
		}

		private function logout(){
			$username = $this->_request['username'];
			$token = $this->_request['token'];
			if (!empty($username) && !empty($token)){
				$logout = $this->pdo->prepare('
					UPDATE
						users
					SET
						token = NULL
					WHERE
						username = :username
					AND
						token = :token
				');
				$logout->execute(array(
					':username' => $username,
					':token' => $token
				));
				$this->response($this->json(array('status'=>'Success')),200);
			} else {
				$this->response($this->json(array('status'=>'Fail','msg'=>'Incorrect parameters')),200);
			}
		}

		/* Get All Blog Posts which arent deleted or drafts, for use on the front end */
		private function posts(){
			$pageNum = (!empty($this->_request['pageNum']) && is_numeric($this->_request['pageNum']) ? $this->_request['pageNum'] * 5 : 0);
			if ($pageNum < 0){
				$pageNum = 0;
			}
			$getBlogPosts = $this->pdo->prepare("
				SELECT
					P.uuid
					,P.slug
					,P.title
					,P.body
					,U.username
					,P.date_added
					,C.title AS category
					,S.title AS status
					,(
						SELECT
							COUNT(P.uuid)
						FROM
							blog_posts P
						JOIN
							blog_status S
						ON
							S.uuid = P.uuidStatus
						JOIN
							users U
						ON
							U.uuid = P.uuidUser
						JOIN
							blog_categories C
						ON
							C.uuid = P.uuidCategory
						WHERE
							P.blnDeleted = 0
						AND
							S.title = 'published'
					) AS total
					,(
						SELECT
							COUNT(*)
						FROM
							blog_comments BC
						WHERE
							uuidPost = P.uuid
					) AS commentCount
					,(
						SELECT
							BC.name
						FROM
							blog_comments BC
						WHERE 
							uuidPost = P.uuid
						ORDER BY
							BC.date_added DESC
						LIMIT 
							0,1
					) AS commentName
					,(
						SELECT
							BC.text
						FROM
							blog_comments BC
						WHERE
							uuidPost = P.uuid
						ORDER BY
							BC.date_added DESC
						LIMIT
							0,1
					) AS commentText
				FROM
					blog_posts P
				JOIN
					blog_status S
				ON
					S.uuid = P.uuidStatus
				JOIN
					users U
				ON
					U.uuid = P.uuidUser
				JOIN
					blog_categories C
				ON
					C.uuid = P.uuidCategory
				WHERE
					P.blnDeleted = 0
				AND
					S.title = 'published'
				GROUP BY
					P.uuid
				ORDER BY
					date_added DESC
				LIMIT :pageNum, 5
			");
			$getBlogPosts->execute(array(
				':pageNum' => $pageNum
			));
			$result = $getBlogPosts->fetchAll(PDO::FETCH_ASSOC);
			$this->response($this->json($result), 200);
			// If success everythig is good send header as "OK" and user details
			if (count($result)){
				$this->response($this->json($result), 200);
			} else {
				$this->response($this->json(array('status'=>'Fail','msg'=>'No records found')), 200);	// If no records "No Content" status
			}
		}

		/* Get All Blog Posts including drafts, for use on the backend */
		private function allPosts(){
			$getAllPosts = $this->pdo->prepare("
				SELECT 
					P.uuid
					,P.slug
					,P.title
					,P.body
					,U.username
					,P.date_added
					,C.title AS category
					,S.title AS status
					,(
						SELECT
							COUNT(P.uuid)
						FROM
							blog_posts P
						JOIN
							blog_status S
							ON
							S.uuid = P.uuidStatus
						JOIN
							users U
							ON U.uuid = P.uuidUser
						JOIN
							blog_categories C
							ON C.uuid = P.uuidCategory
						WHERE
							P.blnDeleted = 0
							AND S.title = 'published'
					) AS total
				FROM
					blog_posts P
				JOIN
					blog_status S
					ON S.uuid = P.uuidStatus
				JOIN
					users U
					ON U.uuid = P.uuidUser
				JOIN
					blog_categories C
					ON C.uuid = P.uuidCategory
				WHERE
					P.blnDeleted = 0
					AND S.title = 'published'
				GROUP BY P.uuid
				ORDER BY date_added DESC
			");
			$getAllPosts->execute();
			$result = $getAllPosts->fetchAll(PDO::FETCH_ASSOC);
			// If success everythig is good send header as "OK" and user details
			if (count($result)){
				$this->response($this->json($result), 200);
			} else {
				$this->response($this->json(array('status'=>'Fail','msg'=>'No records found')), 200);	// If no records "No Content" status
			}
		}

		/* Get Blog Post By ID or SLUG */
		private function post(){
			$postSlug = (!empty($this->_request['slug']) ? $this->_request['slug'] : NULL);
			if (!empty($postSlug)){
				$getSinglePost = $this->pdo->prepare("
					SELECT
						P.uuid
						,P.slug
						,P.title
						,P.body
						,P.date_added
						,P.allowComments
						,U.username
						,C.uuid AS uuidComment
						,C.name
						,C.text
						,C.date_added AS comment_date
						,CAT.title AS category_title
						,CAT.uuid AS category_uuid
						,S.uuid AS status_uuid
						,S.title AS status_title
					FROM
						blog_posts P
					JOIN
						users U
						ON U.uuid = P.uuidUser
					JOIN 
						blog_status S
						ON S.uuid = P.uuidStatus
					JOIN
						blog_categories CAT
						ON CAT.uuid = P.uuidCategory
					LEFT JOIN
						blog_comments C
						ON C.uuidPost = P.uuid
					WHERE
						P.slug = :postSlug
						AND P.blnDeleted = 0
				");
				$getSinglePost->execute(array(
					':postSlug' => $postSlug
				));
				$result = $getSinglePost->fetchAll(PDO::FETCH_ASSOC);
				if(count($result)) {
					// If success everythig is good send header as "OK" and user details
					$this->response($this->json($result), 200);
				} else {
					$this->response($this->json(array('status'=>'Fail','msg'=>'No records found')), 200);	// If no records "No Content" status
				}
			} else {
				$this->response($this->json(array('status'=>'Fail','msg'=>'No records found')), 200);	// If no records "No Content" status
			}
		}

		/* Upsert blog post */
		private function savePost(){
			if($this->validToken($this->_request['token'],$this->_request['uuidUser'])){
				$uuidUser = $this->_request['uuidUser'];
				$slug = $this->_request['slug'];
				$title = $this->_request['title'];
				$body = $this->_request['body'];
				$allowComments = $this->_request['allowComments'];
				$uuidCategory = $this->_request['uuidCategory'];
				$uuidStatus = $this->_request['uuidStatus'];
				$checkPostExists = $this->pdo->prepare("
					SELECT
						slug
						,uuid
					FROM
						blog_posts
					WHERE
						slug = :slug
				");
				$checkPostExists->execute(array(
					':slug' => $slug
				));
				$result = $checkPostExists->fetch(PDO::FETCH_ASSOC);
				if (!empty($result)){
					$uuidPost = $result->uuid;
					$updatePost = $this->pdo->prepare("
						UPDATE
							blog_posts
						SET
							title = :title
							,body = :body
							,allowComments = :allowComments
							,uuidCategory = :uuidCategory
							,uuidStatus = :uuidStatus
						WHERE
							slug = :slug
					");
					$updatePost->execute(array(
						':title' => $title,
						':body' => $body,
						':allowComments' => $allowComments,
						':uuidCategory' => $uuidCategory,
						':uuidStatus' => $uuidStatus,
						':slug' => $slug
					));
					$this->response($this->json(array('status'=>'Success','msg'=>'Post updated')),200);
				} else {
					$uuidPost = $this->generate_uuid();
					$dateNow = date('Y-m-d H:i:s');
					$insertPost = $this->pdo->prepare("
						INSERT INTO
							blog_posts (
								uuid
								,slug
								,title
								,body
								,date_added
								,uuidUser
								,blnDeleted
								,uuidCategory
								,allowComments
								,uuidStatus
							)
						VALUES (
							:uuidPost
							,:slug
							,:title
							,:body
							,:dateNow
							,:uuidUser
							,0
							,:uuidCategory
							,:allowComments
							,:uuidStatus
						)
					");
					$insertPost->execute(array(
						':uuidPost' => $uuidPost,
						':slug' => $slug,
						':title' => $title,
						':body' => $body,
						':dateNow' => $dateNow,
						':uuidUser' => $uuidUser,
						':uuidCategory' => $uuidCategory,
						':allowComments' => $allowComments,
						':uuidStatus' => $uuidStatus,
					));
				}
				if (!empty($this->_request['fileName'])){
					// Get image listOrder
					$checkImage = $this->pdo->prepare("
						SELECT
							MAX(listOrder) AS maxListOrder
						FROM
							blog_images
						WHERE
							uuidPost = :uuidPost
					");
					$checkImage->execute(array(
						':uuidPost'=>$uuidPost
					));
					$listOrder = $checkImage->maxListOrder + 1;
					// Add image
					$addImage = $this->pdo->prepare("
						INSERT INTO
							blog_images (
								uuid
								,uuidPost
								,path
								,blnDeleted
								,listOrder
							)
						VALUES (
							:uuid
							,:uuidPost
							,:path
							,:blnDeleted
							,:listOrder
						)
					");
					$addImage->execute(array(
						':uuid' => $this->generate_uuid(),
						':uuidPost' => $uuidPost,
						':path' => $this->_request['fileName'],
						':blnDeleted' => 0,
						':listOrder' => $listOrder
					));
				}
				$this->response($this->json(array('status'=>'Success','msg'=>'Post added')),200);	
			} else {
				$this->response($this->json(array('status'=>'Failed','msg'=>'User not authenticated')), 200);
			}
		}

		/* Insert comment */
		private function saveComment(){
			if ($this->_request['uuidPost']){
				$uuid = $this->generate_uuid();
				$uuidPost = $this->_request['uuidPost'];
				$name = $this->_request['name'];
				$text = $this->_request['text'];
				$date = date('Y-m-d H:i:s');
				$saveComment = $this->pdo->prepare("
					INSERT INTO
						blog_comments (
							uuid
							,name
							,text
							,date_added
							,blnDeleted
							,uuidPost
						)
					VALUES (
						:uuid
						,:name
						,:text
						,:date
						,0
						,:uuidPost
					)
				");
				$saveComment->execute(array(
					':uuid' => $uuid,
					':name' => $name,
					':text' => $text,
					':date' => $date,
					':uuidPost' => $uuidPost
				));
				$this->response($this->json(array('status'=>'Success','msg'=>'Comment added')),200);
			} else {
				$this->response($this->json(array('status'=>'Failed','msg'=>'Invalid parameters')), 200);
			}
		}

		/* Check Token */
		private function validToken($token, $uuidUser){
			$checkToken = $this->pdo->prepare("
				SELECT
					uuid
					,token
				FROM
					users
				WHERE
					token = :token
					AND uuid = :uuid
			");
			$checkToken->execute(array(
				':token' => $token,
				':uuid' => $uuidUser
			));
			$result = $checkToken->fetch(PDO::FETCH_ASSOC);
			if(count($result)) {
				return true;
			} else {
				return false;
			}

		}

		private function getCategories(){
			$getCategories = $this->pdo->prepare("
				SELECT
					uuid
					,title
					,slug
				FROM
					blog_categories
			");
			$getCategories->execute();
			$result = $getCategories->fetchAll(PDO::FETCH_ASSOC);
			if(count($result)) {
				$this->response($this->json($result), 200);
			} else {
				$this->response($this->json(array('status'=>'Fail','msg'=>'No records found')), 200);	// If no records "No Content" status
			}
		}

		private function saveCategory(){
			if($this->validToken($this->_request['token'],$this->_request['uuidUser'])){
				$uuid = $this->generate_uuid();
				$title = $this->_request['title'];
				$slug = $this->_request['slug'];
				$saveCategory = $this->pdo->prepare("
					INSERT INTO
						blog_categories (
							uuid
							,title
							,slug
						)
					VALUES (
						:uuid
						,:title
						,:slug
					)
				");
				$saveCategory->execute(array(
					':uuid' => $uuid,
					':title' => $title,
					':slug' => $slug
				));
				$this->response($this->json(array('status'=>'Success','msg'=>'Category added')),200);				
			} else {
				$this->response($this->json(array('status'=>'Failed','msg'=>'User not authenticated')), 401);
			}

		}

		private function getStatuses(){
			$getStatuses = $this->pdo->prepare("
				SELECT
					uuid
					,title
				FROM
					blog_status
			");
			$getStatuses->execute();
			$result = $getStatuses->fetchAll(PDO::FETCH_ASSOC);
			if(count($result)) {
				// If success everythig is good send header as "OK" and user details
				$this->response($this->json($result), 200);
			} else {
				$this->response($this->json(array('status'=>'Fail','msg'=>'No records found')), 200);	// If no records "No Content" status
			}
		}



















		// /* Get All Users */
		// private function users(){
		// 	if($this->get_request_method() != 'GET'){
		// 		$this->response('',406);
		// 	}
		// 	$query="SELECT id, username, real_name, email, bio FROM anchor_users";
		// 	$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
		// 	$result = array();
		// 	while($row = $r->fetch_assoc()){
		// 		$result[] = $row;
		// 	}
		// 	// If success everythig is good send header as "OK" and user details
		// 	$this->response($this->json($result), 200);
		// }

		// /* Get User By ID */
		// private function user(){
		// 	if($this->get_request_method() != 'GET'){
		// 		$this->reesponse('',406);
		// 	}
		// 	$userID = $this->_request['id'];
		// 	if(!empty($userID)){
		// 		$query="SELECT id, username, real_name, email, bio FROM anchor_users WHERE id = '$userID'";
		// 		$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
		// 		if($r->num_rows > 0) {
		// 			$result = $r->fetch_assoc();	
		// 			// If success everythig is good send header as "OK" and user details
		// 			$this->response($this->json($result), 200);
		// 		}
		// 		$this->response('', 204);	// If no records "No Content" status
		// 	}
		// 	$error = array('status' => "Failed", "msg" => "User not found");
		// 	$this->response($this->json($error), 400);
		// }

		

		/* Get Approved Blog Post Comments By Blog Post ID */
		// private function comments(){
		// 	$postID = $this->_request['id'];
		// 	if(!empty($postID)){
		// 		$query="SELECT id, post, status, `date`, name, `email`, text FROM anchor_comments WHERE post = '$postID' AND status = 'Approved'";
		// 		$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
		// 		if($r->num_rows > 0) {
		// 			$result = $r->fetch_assoc();	
		// 			// If success everythig is good send header as "OK" and user details
		// 			$this->response($this->json($result), 200);
		// 		}
		// 		$this->response('', 204);	// If no records "No Content" status
		// 	}

		// 	$error = array('status' => "Failed", "msg" => "Post not found");
		// 	$this->response($this->json($error), 400);
		// }

		/*	
		private function customers(){	
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$query="SELECT distinct c.customerNumber, c.customerName, c.email, c.address, c.city, c.state, c.postalCode, c.country FROM angularcode_customers c order by c.customerNumber desc";
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					$result[] = $row;
				}
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('',204);	// If no records "No Content" status
		}
		private function customer(){	
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$id = (int)$this->_request['id'];
			if($id > 0){	
				$query="SELECT distinct c.customerNumber, c.customerName, c.email, c.address, c.city, c.state, c.postalCode, c.country FROM angularcode_customers c where c.customerNumber=$id";
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				if($r->num_rows > 0) {
					$result = $r->fetch_assoc();	
					$this->response($this->json($result), 200); // send user details
				}
			}
			$this->response('',204);	// If no records "No Content" status
		}
		
		private function insertCustomer(){
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}

			$customer = json_decode(file_get_contents("php://input"),true);
			$column_names = array('customerName', 'email', 'city', 'address', 'country');
			$keys = array_keys($customer);
			$columns = '';
			$values = '';
			foreach($column_names as $desired_key){ // Check the customer received. If blank insert blank into the array.
			   if(!in_array($desired_key, $keys)) {
			   		$$desired_key = '';
				}else{
					$$desired_key = $customer[$desired_key];
				}
				$columns = $columns.$desired_key.',';
				$values = $values."'".$$desired_key."',";
			}
			$query = "INSERT INTO angularcode_customers(".trim($columns,',').") VALUES(".trim($values,',').")";
			if(!empty($customer)){
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				$success = array('status' => "Success", "msg" => "Customer Created Successfully.", "data" => $customer);
				$this->response($this->json($success),200);
			}else
				$this->response('',204);	//"No Content" status
		}
		private function updateCustomer(){
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
			$customer = json_decode(file_get_contents("php://input"),true);
			$id = (int)$customer['id'];
			$column_names = array('customerName', 'email', 'city', 'address', 'country');
			$keys = array_keys($customer['customer']);
			$columns = '';
			$values = '';
			foreach($column_names as $desired_key){ // Check the customer received. If key does not exist, insert blank into the array.
			   if(!in_array($desired_key, $keys)) {
			   		$$desired_key = '';
				}else{
					$$desired_key = $customer['customer'][$desired_key];
				}
				$columns = $columns.$desired_key."='".$$desired_key."',";
			}
			$query = "UPDATE angularcode_customers SET ".trim($columns,',')." WHERE customerNumber=$id";
			if(!empty($customer)){
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				$success = array('status' => "Success", "msg" => "Customer ".$id." Updated Successfully.", "data" => $customer);
				$this->response($this->json($success),200);
			}else
				$this->response('',204);	// "No Content" status
		}
		
		private function deleteCustomer(){
			if($this->get_request_method() != "DELETE"){
				$this->response('',406);
			}
			$id = (int)$this->_request['id'];
			if($id > 0){				
				$query="DELETE FROM angularcode_customers WHERE customerNumber = $id";
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				$success = array('status' => "Success", "msg" => "Successfully deleted one record.");
				$this->response($this->json($success),200);
			}else
				$this->response('',204);	// If no records "No Content" status
		}*/
		
		/*
		 *	Encode array into JSON
		*/
		private function json($data){
			if(is_array($data)){
				return json_encode($data);
			}
		}
	}
	
	// Initiiate Library
	
	$api = new API;
	$api->processApi();
?>