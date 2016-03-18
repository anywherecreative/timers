</div>
</div>
<footer class="row-fluid visible-print-block">
<div class="col-md-12">
  <p id="print-date"></p>
</footer>
</div>
<script>
	var server = <?=time();?>;
</script>
<script
src="https://code.jquery.com/jquery-2.2.1.min.js"
integrity="sha256-gvQgAFzTH6trSrAWoH1iPo9Xc96QxSZ3feW6kem+O00="
crossorigin="anonymous"></script>
<script
src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"
integrity="sha256-xNjb53/rY+WmG+4L6tTl9m6PpqknWZvRt0rO1SRnJzw="
crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
<script src="assets/javascript/main.js"></script>
<?php if(isset($js)): ?>
<script src="//<?=SITE_DOMAIN?><?=$this->addJs($js)?>"></script>
<?php endif;?>
<?php
if($this->isVariable('errors')):
?>
<script>
	var error = <?=$this->loadVariable('errors');?>;
</script>
</body>
</html>
