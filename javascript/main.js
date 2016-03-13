(function() {
  var tasks = {};
  $(document).ready(function() {
    if(localStorage.getItem("tasks") != null) {
      tasks = JSON.parse(localStorage.getItem("tasks"));
    }
    if(tasks == null || Object.keys(tasks).length < 1) {
      $('#timers').html("<div class='row-fluid'><div class='col-xs-12'><p class='no-timers'>No Timers Found</p></div></div>");
    }
    else {
      taskView = showDate(Date.now());
      $.each(tasks,function(index,task) {
        var extra = ''
        var totalTime = 0;
        var ds;
        $('#timers').append("<div class='row-fluid timer' id='" + index + "'></div>");
        var item = $('#' + index);
        if(task.description != '') {
          item.append("<div class='col-xs-8 description'>" + task.description + "</div>");
        }
        else {
          item.append("<div class='col-xs-8 description blank'>Click to add a description</div>");
        }
        if(task.start != null) {
          extra = "running";
          ds = "data-start='" + task.start + "'";
          totalTime = task.totalTime + (Date.now()-task.start);
        }
        else {
          totalTime = task.totalTime;
        }
        item.append("<div class='col-xs-2 time " + extra + "' " + ds + " data-time='" + task.totalTime + "'>" + friendlyTime(totalTime) + "</div>");
        if(task.start != null) {
          item.append("<div class='col-xs-2 timer-toggle'><button class='btn btn-danger btn-lg' data-action='stop'>Stop</button><button data-timer='" + index + "' class='delete btn btn-default btn-lg'><i class='fa fa-trash fa-3'></i></button></div>");
        }
        else {
          item.append("<div class='col-xs-2 timer-toggle'><button class='btn btn-success btn-lg' data-action='start'>Start</button><button data-timer='" + index + "' class='delete btn btn-default btn-lg'><i class='fa fa-trash fa-3'></i></button></div>");
        }
      });
      assignAction();
    }

    /**
      start a new timer
    **/
    $('#startTimer').click(function() {
      $('.no-timers').remove();
      var tid = Number(Date.now()).toString(16)
      tasks[tid] = {
        created: Date.now(), //this holds the date the timer was started
        start: Date.now(), //when the timer was last started
        totalTime: 0, //the total time adde to the timer
        description: '', //the description the user has given
        keywords: {} //any keywords the user assigns to timer
      }
      $('#timers').append("<div class='row-fluid timer' id='" + tid + "'></div>");
      var item = $('#'+tid);
      item.append("<div class='col-xs-8 description blank'>Click to add a description</div>");
      item.append("<div class='col-xs-2 time running' data-time='0' data-start='" + Date.now() + "'>00:00:00</div>");
      item.append("<div class='col-xs-2 timer-toggle'><button class='btn btn-lg btn-danger' data-action='stop'>Stop</button><button data-timer='" + tid + "' class='delete btn btn-default btn-lg'><i class='fa fa-trash fa-3'></i></button></div>");
      assignAction();
      saveTimers();
    });
  });

  function friendlyTime(ms) {
    var rawSeconds = Math.round(ms/1000,0)
    var rawMinutes = Math.floor(rawSeconds/60,0); //turn the time into minutes
    var hours = Math.floor(rawMinutes/60); //devide into hours, floor the amount since we'll count it in minutes
    var minutes = rawMinutes%60 //find the minutes by getting the remainder after hours division
    var seconds = rawSeconds%60
    if(hours < 10) {
      hours = "0"+hours //if hours is less then 10 we'll add a leading zero to make it look nice
    }
    if(minutes < 10) {
      minutes = "0"+minutes //we'll add the same leading zero to minutes.
    }
    if(seconds < 10) {
      seconds = "0"+seconds
    }
    hours = hours.toString();
    if(hours.length > 3) {
      //add hundreds seperator, this only applies to hours since the max minutes can be is 59
      var temp = hours;
      hours = "";
      /**
        we've applied the hours figure to temp so we can refill hours with the formatted string
        we'll work backwards through the string, 3 characters at a time adding a comma to the beginning
        after that we'll take the difference that wasn't accounted for in our loop and add it to the beginning
        if there is no difference we'll parse the comma off
      **/
      for(a = temp.length;a >= 3;a-=3) {
        hours = ","+temp.substring(a-3,a)+hours;
      }
      if(temp.length%3 == 0) {
        hours = hours.substring(1);
      }
      else {
        hours = temp.substring(0,temp.length%3)+hours;
      }
    }
    return hours+":"+minutes+":"+seconds;
  }

  function assignAction() {
    $('.timer-toggle BUTTON:not(.delete)').click(function() {
      var item = $(this).parent().parent();
      var id = item.attr('id');
      if($(this).data('action') == "stop") {
        tasks[id].totalTime += Date.now()-tasks[id].start;
        tasks[id].start = null;
        if(!$('.description').hasClass('blank')) {
          tasks[id].description = $('.description',item).text();
        }
        $(this).removeClass('btn-danger');
        $(this).addClass('btn-success');
        $(this).text('Start');
        $(this).data('action','start');
        $('.time',item).removeClass('running');
        $('.time',item).data('time',tasks[id].totalTime);
      }
      else {
        tasks[item.attr('id')].start = Date.now();
        $(this).removeClass('btn-success');
        $(this).addClass('btn-danger');
        $(this).text('Stop');
        $(this).data('action','stop');
        $('.time',item).addClass('running');
        $('.time',item).data('start',Date.now());
        $('.time',item).data('time',tasks[item.attr('id')].totalTime);
        console.log(tasks[item.attr('id')].start);
      }
      saveTimers();
    });
    $('.description').click(function() {
      if($(this).hasClass('blank')) {
        $(this).empty();
        $(this).removeClass('blank');
      }
      $(this).attr('contenteditable','true');
      $(this).focus();
    })
    $('.description').blur(function() {
      $(this).attr('contenteditable','false');
      if($(this).text() == '') {
        $(this).addClass('blank');
        $(this).text("Click to add a description");
        tasks[$(this).parent().attr('id')].description = "";
      }
      else {
        tasks[$(this).parent().attr('id')].description = $(this).text();
      }
      saveTimers();
    });
    $('.timer').click(function() {
      //$(this).toggleClass('selected');
    });
    $('.delete').click(function() {
      if(confirm('Are you sure you want to delete this timer?')) {
        $("#"+$(this).data('timer')).slideUp(300);
        delete tasks[$(this).data('timer')];
      }
      if(tasks == null || Object.keys(tasks).length < 1) {
        $('#timers').html("<div class='row-fluid'><div class='col-xs-12'><p class='no-timers'>No Timers Found</p></div></div>");
      }
      saveTimers();
    })
  }

  function saveTimers() {
    localStorage.setItem("tasks", JSON.stringify(tasks));
  }
  window.setInterval(function() {
    $(".time.running").each(function() {
      $(this).text(friendlyTime($(this).data('time') + (Date.now()-$(this).data('start'))));
    });
  },1000)
  function showDate(day) {

  }

  function showAll() {

  }

})();
