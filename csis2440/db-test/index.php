<?php
	echo $_SERVER['HTTP_HOST'].'<br><br><br>';
	if ($_SERVER['HTTP_HOST'] == 'localhost')
	{
		define('HOST', 'localhost');
		define('USER', 'root');
		define('PASS', '1550');
		define('DB', 'palindromes');
	}
	else
	{
		define('HOST', 'localhost');
		define('USER', 'eghbxxte_rootNelstromo');
		define('PASS', 'n7c/bB93g7-n7c/bB93g7');
		define('DB', 'eghbxxte_palindromes');
	}
	

	//CONNECT TO THE DATABASE
	$conn = mysqli_connect(HOST,USER,PASS,DB);
	
	//WRITE A DB QUERY
	$sql = 'SELECT * FROM palindrome;';
	
	//RUN DB QUERY 
	$results = mysqli_query($conn, $sql);

	
	//LOOP THROUGH THE DATA 
	while ($row = mysqli_fetch_array($results, MYSQLI_ASSOC))
	{
		echo $row['phrase'].'<br>';
	};


?>