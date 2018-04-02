<?php
header('Access-Control-Allow-Methods: POST, GET, DELETE');
$url     = $_GET['url'];
$datearr = array();

if ($url == '') {

?>

<!DOCTYPE HTML>
<html>

<head>
    <link rel="stylesheet" type="text/css" href="cssFile.css">
    <script src="https://canvasjs.com/assets/script/jquery-1.11.1.min.js"></script>
    <script src="https://canvasjs.com/assets/script/jquery.canvasjs.min.js"></script>
    <script>
        function lodeGraph(responseData, div) {
            var dps = [];
            if (div == 'API') {
                var displayText = "Forecasts from API created by me";
                var displaySubText = "For the Five Days after given date";
            } else {
                var displayText = "Forecasts from Weather Underground";
                var displaySubText = "api.wunderground.com/api - OH/Cincinnati - current date";
            }
            for (var i = 0; i < 5; i++) {
                var str = responseData[i]['DATE'];
                var min = responseData[i]['TMIN'];
                var max = responseData[i]['TMAX'];
                xVal = new Date(str.substring(0, 4), Number(str.substring(4, 6)) - 1, str.substring(6, 8));
                yVal = [Number(min), Number(max)];
                dps.push({
                    x: xVal,
                    y: yVal
                });
            }

            var options = {
                exportEnabled: true,
                animationEnabled: true,
                title: {
                    text: displayText,
                    fontColor: "#aa80ff",
                    fontSize: 25,
                    fontFamily: "arial"
                },
                subtitles: [{
                    text: displaySubText,
                    fontColor: "#b3b3ff",
                    fontSize: 15,
                    fontFamily: "arial"
                }],
                axisX: {
                    title: "Date",
                    valueFormatString: "DD-MMM-YYYY",
                    labelFontColor: "#0040ff",
                    titleFontColor: "#0033cc"

                },
                axisY: {
                    title: "Temperature",
                    labelFontColor: "#0040ff",
                    titleFontColor: "#0033cc"
                },
                data: [{
                    type: "rangeSplineArea",
                    indexLabel: "{y[#index]}",
                    xValueFormatString: "DD-MMM-YYYY",
                    toolTipContent: "<b>DATE:{x}</b></br>TMAX:{y[1]},TMIN:{y[0]}",
                    color: "#b3c6ff",
                    dataPoints: dps
                }]
            };
            if (div == 'API') {
                var strdate = responseData[0]['DATE'];
                var dateGMT = new Date(strdate.substring(0, 4), Number(strdate.substring(4, 6)) - 1, strdate.substring(6, 8));

                $("#chartContainerAPI").CanvasJSChart(options);
                document.getElementById("demo").innerHTML = "<b>SUCCESS:"+dateGMT +":TMAX = "+responseData[0]['TMAX']+" & TMIN = 	"+responseData[0]['TMIN']+"</b>";
            } else {
                $("#chartContainerUG").CanvasJSChart(options);
            }
        }

        // Validates that the input string is a valid date formatted as "yyyymmdd"
        function isValidDate(dateString) {
            // First check for the pattern
            if (!/^\d{8}$/.test(dateString))
                return false;

            // Parse the date parts to integers
            var day = dateString.substring(6, 8);
            var month = dateString.substring(4, 6);
            var year = dateString.substring(0, 4);

            // Check the ranges of month and year
            if (year < 1000 || year > 3000 || month == 0 || month > 12)
                return false;

            var monthLength = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

            // Adjust for leap years
            if (year % 400 == 0 || (year % 100 != 0 && year % 4 == 0))
                monthLength[1] = 29;

            // Check the range of the day
            return day > 0 && day <= monthLength[month - 1];
        };

        function loadDoc() {
            if (document.getElementById("enterdate").value) {
                var date = document.getElementById("enterdate").value;
                if (!isValidDate(date)) {
                    document.getElementById("demo").style.color = "#ff0000";
                    document.getElementById("demo").innerHTML = "<b>ERROR : Invalid DATE entered</b>";
                    return;
                }
            } else {
                document.getElementById("demo").style.color = "#ff0000";
                document.getElementById("demo").innerHTML = "<b>ERROR : Please enter a DATE</b>";
                return;
            }

            var xhttp = new XMLHttpRequest();
            var url = "http://18.218.244.0/forecast/" + date;
            xhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("demo").style.color = "green";
                    lodeGraph(JSON.parse(this.responseText), 'API');
                }
            };
            xhttp.open("GET", url, true);
            xhttp.send();
        }

        window.onload = function() {
            var xhttps = new XMLHttpRequest();
            var url = "http://18.218.244.0/underground";
            xhttps.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("demo").style.color = "green";
                    document.getElementById("demo").innerHTML = "<b>SUCCESS : Request from -</b> http://api.wunderground.com/api/0e89f92d515673b5/geolookup/forecast10day/q/OH/Cincinnati.json";
                    lodeGraph(JSON.parse(this.responseText), 'UG');
                }
            };
            xhttps.open("GET", url, true);
            xhttps.send();
        }
    </script>
</head>

<body align="center" style="background-color: #f0f0f5;background-size: cover;" background="1.jpg">
    <h2>Plot the Forecasts for the next five days</h2> Enter the date in YYYYMMDD format :
    <input type="text" name="enterdate" placeholder="YYYYMMDD" id="enterdate"><br>
    <button type="button" onclick="loadDoc()">Plot data</button>
    <p id="demo"></p>
    <div id="chartContainerUG" style="height: 400px; width: 40%; display: inline-block;"></div>
    <div id="chartContainerAPI" style="height: 400px; width: 40%; display: inline-block;"></div>
</body>

</html>  

<?php
	
}

if ($url == 'underground') {
    $QueryForecast = file_get_contents("http://api.wunderground.com/api/0e89f92d515673b5/geolookup/forecast10day/q/OH/Cincinnati.json");
    $f = 0;
    $ForecastArray = array();
    $QueryForecastDecode = json_decode($QueryForecast);
    $city = $QueryForecastDecode->{"location"}->{"city"} . "(" . $QueryForecastDecode->{"location"}->{"state"} . ")";
    while ($QueryForecastDecode->{"forecast"}->{"simpleforecast"}->{"forecastday"}[$f]) {
        $Forecasts = $QueryForecastDecode->{"forecast"}->{"simpleforecast"}->{"forecastday"}[$f];
        $day = (str_pad($Forecasts->{"date"}->{"day"}, 2, '0', STR_PAD_LEFT));
        $month = (str_pad($Forecasts->{"date"}->{"month"}, 2, '0', STR_PAD_LEFT));
        $date = $Forecasts->{"date"}->{"year"} . $month . $day;
        $ForecastArray[$f] = array(
            "DATE" => $date,
            "TMAX" => $Forecasts->{"high"}->{"celsius"},
            "TMIN" => $Forecasts->{"low"}->{"celsius"}
        );
        $f++;
    }
    echo json_encode($ForecastArray);
}

if ($url == 'historical/') {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $requestData = file_get_contents('php://input');
        $obj         = json_decode($requestData);
        $addData     = $obj->DATE . ',' . $obj->TMAX . ',' . $obj->TMIN;
        $addData     = trim($addData, ',');
        $addData     = trim($addData);
        if (!empty($addData)) {
            $myfile = fopen("daily.csv", "a") or die("Unable to open file!");
            fwrite($myfile, PHP_EOL . $addData);
            $datearr['DATE'] = $obj->DATE;
            if (substr($sapi_type, 0, 3) == 'cgi')
                header("Status: 201 Success");
            else
                header("HTTP/1.1 201 Success");
            echo json_encode($datearr);
            fclose($myfile);
        }
    }
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $myfile = fopen("daily.csv", "r") or die("Unable to open file!");
        while (!feof($myfile)) {
            $datestr           = fgets($myfile);
            $arr               = explode(',', $datestr);
            $arr[0] = trim($arr[0]);
            if(!empty($arr[0]))
                $datearr[]['DATE'] = $arr[0];
        }
        echo json_encode($datearr);
        fclose($myfile);
    }
}

if (preg_match("/^historical\/[0-9]{8}$/", trim($url))) {
    $dateUrl = explode('/', $url);
    $date    = $dateUrl[1];
    if ($_SERVER["REQUEST_METHOD"] == "GET") {
        $flag = 0;
        $myfile = fopen("daily.csv", "r") or die("Unable to open file!");
        while (!feof($myfile)) {
            $datestr = fgets($myfile);
            $arr     = explode(',', $datestr);
            if ($arr[0] == $date) {
                $datearr["DATE"] = trim($arr[0]);
                $datearr["TMAX"] = trim($arr[1]);
                $datearr["TMIN"] = trim($arr[2]);
                $flag            = 1;
                break;
            }
        }
        if ($flag == 1) {
            echo json_encode($datearr);
        } else {
            $sapi_type = php_sapi_name();
            if (substr($sapi_type, 0, 3) == 'cgi')
                header("Status: 404 Not Found");
            else
                header("HTTP/1.1 404 Not Found");
            echo json_encode('404:entry not found');
        }
        fclose($myfile);
    }
    
    if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
        $flag = 0;
        $out  = array();
        $myfile = fopen("daily.csv", "r") or die("Unable to open file!");
        while (!feof($myfile)) {
            $datestr = fgets($myfile);
            $arr     = explode(',', $datestr);
            if ($arr[0] != $date) {
                $out[] = $datestr;
            } else {
                $flag = 1;
            }
        }
        fclose($myfile);
        if ($flag == 1) {
            $myfile = fopen("daily.csv", "w") or die("Unable to open file!");
            foreach ($out as $line) {
                fwrite($myfile, $line);
            }
            fclose($myfile);
        }
        echo json_encode('200:Success');
    }
}

if (preg_match("/forecast\/[0-9]{8}$/", trim($url))) {
    $forecast = 0;
    $dateUrl  = explode('/', $url);
    $date     = $dateUrl[1];
    $myfile = fopen("daily.csv", "r") or die("Unable to open file!");
    // Output one line until end-of-file    
    while (!feof($myfile)) {
        $datestr = fgets($myfile);
        $arr     = explode(',', $datestr);
        if ($forecast == 7) {
            break;
        }
        if ($arr[0] == $date) {
            $datearr[$forecast]["DATE"] = trim($arr[0]);
            $datearr[$forecast]["TMAX"] = trim($arr[1]);
            $datearr[$forecast]["TMIN"] = trim($arr[2]);
            $date                       = getNextDate($date);
            $forecast += 1;
        }
    }
	$j = 1.1;
    if ($forecast < 7) {
        for ($i = 0; $i < 7; $i++) {
	    $j = round($j * -1.2 * ($i+0.1), 2);
            if (!$datearr[$i]) {
                $datearr[$i]["DATE"] = trim($date);
                $datearr[$i]["TMAX"] = trim(fmod(substr($date, 6, 2)+11.1 + $j + $i / 10 , 35));
                $datearr[$i]["TMIN"] = round(trim(fmod(substr($date, 6, 2) + $j + $i / 10, 15)), 2);
		if($datearr[$i]["TMAX"] < $datearr[$i]["TMIN"]){
			$temp = $datearr[$i]["TMAX"];
			$datearr[$i]["TMAX"] = $datearr[$i]["TMIN"];
			$datearr[$i]["TMIN"] = $temp;
		}
                $date                = getNextDate($date);
            }
        }
    }
    echo json_encode($datearr);
    fclose($myfile);
}

function getNextDate($date)
{
    $year  = substr($date, 0, 4);
    $month = substr($date, 4, 2);
    $day   = substr($date, 6, 2);
    if (checkdate($month, $day + 1, $year)) {
        $day  = (str_pad($day + 1, 2, '0', STR_PAD_LEFT));
        $date = $year . $month . $day;
    } elseif (checkdate($month + 1, 1, $year)) {
        $month = (str_pad($month + 1, 2, '0', STR_PAD_LEFT));
        $date  = $year . $month . '01';
    } else {
        $date = ($year + 1) . '01' . '01';
    }
    return $date;
}
?>