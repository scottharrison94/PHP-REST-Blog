<?php
 	require_once("Rest.inc.php");
	
	class API extends REST {
	
		public $data = "";
		
		const DB_SERVER = "127.0.0.1";
		const DB_USER = "root";
		const DB_PASSWORD = "123";	
		const DB = "blog";

		private $db = NULL;
		private $mysqli = NULL;
		public function __construct(){
			parent::__construct();				// Init parent contructor
			$this->dbConnect();					// Initiate Database connection
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
		 *  Connect to Database
		*/
		private function dbConnect(){
			$this->mysqli = new mysqli(self::DB_SERVER, self::DB_USER, self::DB_PASSWORD, self::DB);
			mysqli_set_charset($this->mysqli,'utf8');
		}
		
		/*
		 * Dynmically call the method based on the query string
		 */
		public function processApi(){
			$func = strtolower(trim(str_replace("/","",$_REQUEST['x'])));
			if((int)method_exists($this,$func) > 0)
				$this->$func();
			else
				$this->response('',404); // If the method not exist with in this class "Page not found".
		}

		private function login(){
			$username = $this->_request['username'];
			$password = $this->_request['password'];

			if(!empty($username) && !empty($password)){
				$query="SELECT uuid, username, password FROM users WHERE username = '$username'LIMIT 1";
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

				if($r->num_rows > 0) {
					$result = $r->fetch_assoc();
					$savedPassword = $result['password'];
					if(password_verify($password,$savedPassword)){
						// Passwords match so add a token to db
						$token = $this->generate_uuid();
						$userUUID = $result['uuid'];
						$query="UPDATE users SET token = '$token' WHERE uuid = '$userUUID'";
						$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
						$result['token'] = $token;
						$this->response($this->json(array('token'=>$result['token'],'uuidUser'=>$result['uuid'])), 200);
					} else {
						$this->response('', 404);
					}
				}
			}
		}

		private function logout(){
			$username = $this->_request['username'];
			$token = $this->_request['token'];
			if (!empty($username) && !empty($token)){
				$query = "UPDATE users SET token = '' WHERE username = '$username' AND token = '$token'";
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				$this->response($this->json(array('status'=>'Success')),200);
			} 
		}

		/* Get All Blog Posts */
		private function posts(){
			$query="SELECT P.uuid, P.slug, P.title, P.body,U.username, P.date_added FROM posts P JOIN users U ON U.uuid = P.uuidUser WHERE P.blnPublished = 1 AND P.blnDeleted = 0 ORDER BY date_added DESC";
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
			$result = array();
			while($row = $r->fetch_assoc()){
				$result[] = $row;
			}
			// If success everythig is good send header as "OK" and user details
			if (count($result)){
				$this->response($this->json($result), 200);
			} else {
				$this->response('', 204);	// If no records "No Content" status
			}
		}

		/* Get Blog Post By ID or SLUG */
		private function post(){
			$postSlug = (!empty($this->_request['slug']) ? $this->_request['slug'] : NULL);
			if (!empty($postSlug)){
				$query="SELECT P.uuid,P.slug, P.title, P.body, P.date_added, U.username, C.uuid AS uuidComment, C.name, C.text FROM posts P JOIN users U ON U.uuid = P.uuidUser LEFT JOIN comments C ON C.uuidPost = P.uuid WHERE P.slug = '$postSlug' AND P.blnDeleted = 0 LIMIT 1";		
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				if($r->num_rows > 0) {
					$result = $r->fetch_assoc();
					// If success everythig is good send header as "OK" and user details
					$this->response($this->json($result), 200);
				} else {
					$this->response('', 204);	// If no records "No Content" status
				}
			} else {
				$this->response('', 204);	// If no records "No Content" status
			}
		}

		/* Upsert blog post */
		private function savePost(){
			$uuidUser = 'a0b48f2a-1bb9-47fc-8c69-66100bab78dc';
			$slug = 'blog2';//(!empty($this->_request['slug']) ? $this->_request['slug'] : NULL);
			$title = 'This is the title';//$this->_request['title'];
			$body = '<h2>Opening</h2><p><strong>This is the opening paragraph. Lorem ipsum Laborum in in veniam in cupidatat dolore commodo incididunt officia et esse dolore non.</strong></p><p>Lorem ipsum Deserunt Duis laboris ex in commodo commodo ad commodo in ullamco enim sint occaecat nulla cupidatat incididunt in consequat tempor aliquip sed officia tempor Excepteur sint ea occaecat veniam irure labore Duis proident in sunt et enim labore sit voluptate aliqua ad occaecat magna aute ullamco ut voluptate nostrud sint nulla eu Excepteur consequat incididunt consectetur dolor minim aute ad incididunt laborum amet cillum elit dolore commodo proident nostrud amet anim id ut Excepteur adipisicing nisi dolor reprehenderit Excepteur esse pariatur minim proident labore anim pariatur elit sunt aliquip labore in consectetur et minim veniam in in id eiusmod proident pariatur fugiat eiusmod dolore in dolore Duis reprehenderit ut aliquip et consectetur sit incididunt commodo aute qui sed esse consequat elit aliquip cillum aliqua elit sunt magna ea esse amet deserunt consequat occaecat eiusmod nisi veniam in eu ullamco aute in minim in commodo nulla exercitation eu nisi dolor cupidatat in reprehenderit qui occaecat enim eu sit nisi ullamco enim sed deserunt labore qui cillum id veniam reprehenderit qui aliquip adipisicing eu aliquip mollit enim minim.</p><p>Lorem ipsum Voluptate dolor do Duis ut consectetur sint dolore non consectetur magna proident ad occaecat ullamco nulla deserunt ad exercitation nostrud laborum sed ut amet velit eu nisi ex commodo consequat ut nulla quis Excepteur ex dolor aliqua irure eu dolor sint aliquip nisi culpa cillum anim cupidatat incididunt pariatur qui laboris dolor aute et Excepteur culpa fugiat labore Duis officia ex nostrud aliqua nisi non occaecat eu nulla ut esse nostrud dolore Duis eiusmod sed eiusmod occaecat ullamco enim labore sed reprehenderit esse tempor non reprehenderit minim ad laboris minim mollit sit in et cupidatat dolor quis eu aute officia irure anim sit sunt nisi ut enim nisi ullamco aliqua dolor aliqua velit Duis enim cupidatat ut consectetur ut qui in aute nisi nisi aute laboris laboris exercitation ad mollit sunt commodo ad id ullamco veniam elit aute eu Duis reprehenderit tempor voluptate elit ut commodo Duis proident dolore tempor enim ut qui laborum occaecat dolore pariatur in proident sed aliquip enim culpa id culpa dolore culpa velit est sit ex tempor incididunt in elit aliquip sint enim in nostrud in nostrud pariatur cupidatat aliqua Excepteur quis ut voluptate non proident veniam occaecat ut sit adipisicing magna in culpa sed amet mollit exercitation voluptate enim anim esse ut dolore consequat irure adipisicing consequat do in commodo eiusmod in anim est consectetur incididunt ut in tempor et velit deserunt eiusmod culpa aliqua magna et eu aliquip velit consectetur enim irure nostrud ullamco est.</p>';//$this->_request['body'];
			$query = "SELECT slug FROM posts WHERE slug = '$slug'";
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
			if ($r->num_rows > 0){
				$query = "UPDATE posts SET title = '$title', body = '$body' WHERE slug = '$slug'";
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				$this->response($this->json(array('status'=>'Success','msg'=>'Post updated')),200);
			} else {
				$uuidPost = $this->generate_uuid();
				$dateNow = date('Y-m-d H:i:s');
				$query = "INSERT INTO posts (uuid,slug,title,body,blnPublished,date_added,uuidUser,blnDeleted) VALUES ('$uuidPost','$slug','$title','$body',0,'$uuidUser','$dateNow',0)";
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				$this->response($this->json(array('status'=>'Success','msg'=>'Post added')),200);				
			}
		}

		/* Insert comment */



















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