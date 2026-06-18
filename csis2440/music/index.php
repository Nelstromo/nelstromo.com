<!doctype html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Nelson's Favorite Music</title>
		<link rel="stylesheet" href="css/style.css" type="text/css">
	</head>

	<body>
		<h1>Indisputable Best Music Albums*</h1>
		<h4>*As ranked by the current position of the stars, moon, sun, and general vibes</h4>
		
		<?php
		
		//Create A Shuffled Number
		
		//Arrays with Album name, Art, Artist, url Links, fave song, release year -- Note, postition in arrays correspond with each other
		$albums = array("Smoke And Mirrors","All This Bad Blood","Dreamland","The Resistance","Everything Now","Before The Waves","saintmotelevision","El Camino","Gone Now","Future Nostalgia");
		$albumArt = array("SmokeAndMirrors.jpg","AllThisBadBlood.jpg","Dreamland.jpg","TheResistance.jpg","EverythingNow.jpg","BeforeTheWaves.jpg","saintmotelevision.jpg","ElCamino.jpg","GoneNow.jpg","FutureNostalgia.jpg");
		$albumArtist = array("Imagine Dragons","Bastille","Glass Animals","Muse","Arcade Fire","Magic Man","Saint Motel","The Black Keys","Bleachers","Dua Lipa");
		$albumFavSong = array("Warriors","Icarus","I don't want to talk (I just want to dance)","Uprise","Put your money on me","Paris","For Elise","Little Black Submarines","All My Heroes","Training Day");
		$albumYoutubeLinks = array("https://www.youtube.com/channel/UCT9zcQNlyht7fRlcjmflRSA","https://www.youtube.com/channel/UC0q_PBWOkFe3SKllFXf3eWw","https://www.youtube.com/channel/UCJTs-KheOMNstaGrDL4K55Q","https://www.youtube.com/channel/UCGGhM6XCSJFQ6DTRffnKRIw","https://www.youtube.com/channel/UCIIGxQ6BA9MwIJXBu47SyZQ","https://www.youtube.com/channel/UCqxkGjCQ8zgJ4DnHvQgdVHA","https://www.youtube.com/@saintmotel","https://www.youtube.com/channel/UCJL3h2-wEOB6EigQOBZ3ryg","https://www.youtube.com/channel/UCjd8rtustMHutt_vrVhsUyg","https://www.youtube.com/channel/UC-J-KZfRV8c13fOCkhXdLiQ");
		$albumReleased = array("2015","2014","2020","2009","2017","2014","2016","2011","2017","2020");
		
		//create a random order
		$order = range(0, 9);
		shuffle($order); // Randomize the order

		// Output the albums in the shuffled order
		foreach ($order as $i) {echo output($albums[$i], $albumArtist[$i], $albumArt[$i], $albumFavSong[$i], $albumYoutubeLinks[$i], $albumReleased[$i]);}

		?>
	</body>
</html>


<?php
	function output($albums, $albumArtist, $albumArt, $albumFavSong, $albumYoutubeLinks, $albumReleased)
	{


    $return = "<div class='album'>";
    $return .= "<h2><a href='$albumYoutubeLinks' target='_blank'>\"$albums\"</a></h2>";
    
    // Container for image and info to align horizontally
    $return .= "<div class='album-content'>";
    
    // Image div
    $return .= "<div class='album-image'>";
    $return .= "<img style='max-width: 300px; max-height: 300px;' src='img/$albumArt' alt='Album art for $albums'>";
    $return .= "</div>";
    
    // Info div
    $return .= "<div class='album-info'>";
    $return .= "<ul>";
    $return .= "<li><strong>Artist:</strong> $albumArtist</li>";
    $return .= "<li><strong>Favorite Song:</strong> $albumFavSong</li>";
    $return .= "<li><strong>Album Released:</strong> $albumReleased</li>";
    $return .= "</ul>";
    $return .= "</div>";
    
    $return .= "</div>"; 
    $return .= "</div>"; 

    return $return;

	}
?>
