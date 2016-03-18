<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-XdYbMnZ/QjLh6iI4ogqCTaIjrFk87ip+ekIjefZch0Y+PvJ8CDYtEs1ipDmPorQ+" crossorigin="anonymous">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <link href="https://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css" rel="stylesheet" />
    <link rel="stylesheet" href="assets/css/main.css" />
    <?=$this->loadHead();?>
  </head>
  <body>
    <nav class="navbar navbar-default">
      <div class="container-fluid">
        <div class="navbar-header">
          <a class="navbar-brand" href="/">
            Time Tracker
          </a>
        </div>
        <form class="navbar-form navbar-left">
          <div class="form-group">
            <label for="datepicker">Showing date:</label>
            <input type="text" class="form-control" id="datepicker" placeholder="Date">
          </div>
        </form>
      </div>
    </nav>
    <div class="container-fluid">
      <div class="row-fluid">
        <div class="col-md-8 col-md-offset-2 main">
          <?php if($this->isVariable('error-message')): ?>
  				<div class="error-message-container">
  					<p id='sys-danger-msg' class='wrapper'>
  						<button type="button" class="close" aria-label="Close" data-dismiss="#sys-danger-msg"><span aria-hidden="true">&times;</span></button>
  						<?=$this->loadVariable('error-message');?>
  					</p>
  				</div>
  				<?php endif; ?>
  				<?php if($this->isVariable('info-message')): ?>
  				<div class="error-message-container">
  					<p id='sys-info-msg' class='wrapper'>
  						<button type="button" class="close" aria-label="Close" data-dismiss="#sys-info-msg"><span aria-hidden="true">&times;</span></button>
  						<?=$this->loadVariable('info-message');?>
  					</p>
  				</div>
  				<?php endif; ?>
  				<?php if($this->isVariable('success-message')): ?>
  				<div class="error-message-container">
  					<p id='sys-success-msg' class='wrapper'>
  						<button type="button" class="close" aria-label="Close" data-dismiss="#sys-success-msg"><span aria-hidden="true">&times;</span></button>
  						<?=$this->loadVariable('success-message');?>
  					</p>
  				</div>
  				<?php endif; ?>
