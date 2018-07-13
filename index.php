<?php
    error_reporting(E_ALL);
    //ini_set("display_errors", 1);

    $history = array_map(null, glob('js/history/*.json'));
    $historyData = [];
    $langJson = file_get_contents('js/lng.json');
    $langJsonParsed = json_decode($langJson, false);
    
    
    foreach($history as $url) {
        $statsJson = file_get_contents($url);
        $statsJsonParsed = json_decode($statsJson, true);
        $date = $statsJsonParsed['date'];
        $language = [];
        for($i = 0; $i<count($statsJsonParsed['rows']); $i++ ) {
            array_push($language, $statsJsonParsed['rows'][$i]['title']);
        }
        $languageUnique = array_unique($language);
        $count4Lang = [];
        foreach($languageUnique as $uniqueLanguageName) {
            $languageCount = 0;
            for($x = 0; $x<count($statsJsonParsed['rows']); $x++ ) {
                if ($statsJsonParsed['rows'][$x]['title'] == $uniqueLanguageName && $statsJsonParsed['rows'][$x]['projectName'] != 'ONLYOFFICEORG') {
                    $languageCount += $statsJsonParsed['rows'][$x]['count(*)'];
                }
            }
            if($languageCount != 0) {
                array_push($count4Lang, array($uniqueLanguageName, $languageCount));
            }
        }
        array_push($historyData, $date, $count4Lang);
    }
    $newArray1 = [];
    foreach($languageUnique as $uniqueLanguageName) {
        $newArray = [];
        for($i = 1; $i<count($historyData); $i+=2) {
            $date = $historyData[$i-1];
            foreach($historyData[$i] as $a => $b) {
                if($b[0] == $uniqueLanguageName) {
                    array_push($newArray, array($b[1], $date));
                }
            }
        }
        array_push($newArray1, $uniqueLanguageName, $newArray);
    }
    file_put_contents('js/history_data.json', json_encode($newArray1));
?>
<html>
<head>
    <title>Create and view language progress charts</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:900,800,700,600,500,400,300&amp;subset=latin,cyrillic-ext,cyrillic,latin-ext" rel="stylesheet" type="text/css" />
    <link href="css/style.css" rel="stylesheet" type="text/css" />
</head>
<body>
<form action="" method="get">
    <div class="formDiv">
        <select name="country">
            <option value="">Select language</option>
            <?php
                foreach($langJsonParsed as $langAlias => $langFull) {
                    echo '<option ';
                    if($_GET['country'] == $langAlias) {
                        echo 'selected';
                        $currLang = $langFull;
                    }
                    echo ' value="'.$langAlias.'">'.$langFull.'</option>';
                }
            ?>
        </select>
        <input name="submitEditor" id="button" type="submit" value="Start!">
    </div>
</form>
    <p id="back-top" style="display: none">
        <a title="Scroll up" href="#top"></a>
    </p>
    <h1>Create and view language progress charts</h1>
    <p>Select a language from the list and click <b>Start!</b> to display the language state and the translation progress.</p>
    <p></p>
    <canvas id="myChart" width="100" height="25"></canvas>
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script type="text/javascript" src="js/arrowup.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.min.js"></script>

    <?php
        if(isset($_GET['submitEditor'])) {
            $languageLocale = $_GET['country'];
            
            if ($languageLocale != '') {
                echo '<script type="text/javascript">';
                echo '$.fn.createChart = function (langname) {
                    $.getJSON("/js/history_data.json", function(data) {
                        var ctx = "myChart", languageArray = [], labelsArray = [], languageArrayNeutral = [], labelsArrayNeutral = [], lengthi = data.length;
                        for (var i=0; i<lengthi; i += 2) {
                            if(data[i] == langname) {
                                for(var x=0, lengthx = data[i+1].length; x<lengthx; x++) {
                                    languageArray.push(data[i+1][x][0]);
                                    labelsArray.push(data[i+1][x][1]);
                                }
                            };
                        }
                        var lengthi = data.length;
                        for (var i=0; i<lengthi; i += 2) {
                            if(data[i] == "Neutral") {
                                for(var x=0, lengthx = data[i+1].length; x<lengthx; x++) {
                                    languageArrayNeutral.push(data[i+1][x][0]);
                                    labelsArrayNeutral.push(data[i+1][x][1]);
                                }
                            };
                        }
                        var myChart = new Chart(ctx, {
                            type: "line",
                            data: {
                                labels: labelsArray, labelsArrayNeutral,
                                datasets: [{
                                    label: "' . $currLang . '",
                                    data: languageArray,
                                    backgroundColor: "rgba(54, 162, 235, 0.2)",
                                    borderColor: "rgba(54, 162, 235, 1)"
                                },
                                {
                                    label: "English",
                                    data: languageArrayNeutral,
                                    backgroundColor: "rgba(255, 206, 86, 0.2)",
                                    borderColor: "rgba(255, 206, 86, 1)"
                                }]
                            },
                            options: {
                                tooltips: {
                                    bodyFontSize: 14,
                                    titleFontSize: 14
                                },
                                elements: {
                                    line: {
                                        tension: 0,
                                    }
                                },
                                legend: {
                                    labels: {
                                        fontSize: 14
                                    }
                                },
                                scales: {
                                    xAxes: [{
                                        type: "time",
                                        ticks: {
                                            fontSize: 14
                                        }
                                    }],
                                    yAxes: [{
                                        ticks: {
                                            fontSize: 14,
                                            beginAtZero: true
                                        }
                                    }]
                                }
                            }
                        });
                    });  
                }
                ';
echo 'function GetURLParameter(sParam)
	{
	    var sPageURL = window.location.search.substring(1);
	    var sURLVariables = sPageURL.split("&");
	    for (var i = 0; i < sURLVariables.length; i++)
	    {
	        var sParameterName = sURLVariables[i].split("=");
	        if (sParameterName[0] == sParam)
	        {
	            return sParameterName[1];
	        }
	    }
	}
$(document).ready(function(){
    var langname = GetURLParameter("country");
    $("#myChart").createChart(langname);
});';
                echo '</script>';
            }
        }
    ?>
</body>
</html>