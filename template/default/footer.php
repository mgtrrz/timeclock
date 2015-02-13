<?php if (!defined('TIMECLOCK')) die(); ?>
    </div> <!-- /container -->

<script type="text/javascript">

$(document).ready(function(){
    $(".close").click(function(){
        $(".alert").alert();
    });
    
    
    $('#clickable tr').click(function() {
        var href = $(this).find("a").attr("href");
        if(href) {
            window.location = href;
        }
    });
    
    window.setTimeout(function() {
        $(".alert").fadeTo(1500, 0).slideUp(500, function(){
            $(this).remove(); 
        });
    }, 5000);
    
    
});  

$(function () {
    $("[rel='tooltip']").tooltip();
});

function limitText(limitField, limitCount, limitNum) {
	if (limitField.value.length > limitNum) {
		limitField.value = limitField.value.substring(0, limitNum);
	} else {
		limitCount.value = limitNum - limitField.value.length;
	}
}


function plz(digit){
 
    var zpad = digit + '';
    if (digit < 10) {
        zpad = "0" + zpad;
    }
    return zpad;
}


function updateClock ()
{
  var currentTime = new Date ();

  var currentHours = currentTime.getHours ();
  var currentMinutes = currentTime.getMinutes ();
  var currentSeconds = currentTime.getSeconds ();

  // Pad the minutes and seconds with leading zeros, if required
  currentMinutes = ( currentMinutes < 10 ? "0" : "" ) + currentMinutes;
  currentSeconds = ( currentSeconds < 10 ? "0" : "" ) + currentSeconds;

  // Choose either "AM" or "PM" as appropriate
  var timeOfDay = ( currentHours < 12 ) ? "AM" : "PM";

  // Convert the hours component to 12-hour format if needed
  currentHours = ( currentHours > 12 ) ? currentHours - 12 : currentHours;

  // Convert an hours component of "0" to "12"
  currentHours = ( currentHours === 0 ) ? 12 : currentHours;

  // Compose the string for display
  var currentTimeString = currentHours + ":" + currentMinutes + ":" + currentSeconds + " " + timeOfDay;

  // Update the time display
  document.getElementById("clock").firstChild.nodeValue = currentTimeString;
  
  
  var time_shown = $("#realtime").text();
  var time_chunks = time_shown.split(":");
  var hour, mins, secs;

  hour=Number(time_chunks[0]);
  mins=Number(time_chunks[1]);
  secs=Number(time_chunks[2]);
  secs++;
  if (secs==60){
    secs = 0;
    mins=mins + 1;
  } 
  if (mins==60){
    mins=0;
    hour=hour + 1;
  }

  $("#realtime").text(plz(hour) +":" + plz(mins) + ":" + currentSeconds);
  
}

// -->
</script>
  </body>
</html>
