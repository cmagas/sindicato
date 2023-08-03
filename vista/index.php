<?php
  session_start();
  include_once("latis/conexionBD.php"); 


  $usuario="";
  $nomUsuario="";
	
	if(!isset($_SESSION['idUsr'])){
        header('Location: ../Login/index.php');
    }
    else{
      $usuario=$_SESSION['idUsr'];
      $nombreUsuario=obtenerNombreUsuario($_SESSION['idUsr']);
      $nombreCompleto=obtenerNombreUsuarioCompleto($_SESSION['idUsr']);
    }
    
   
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>SIDEPEV | Admin</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.7 -->
  <link rel="stylesheet" href="../Plantilla/bower_components/bootstrap/dist/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../Plantilla/bower_components/font-awesome/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="../Plantilla/bower_components/Ionicons/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../Plantilla/dist/css/AdminLTE.min.css">
  <!-- AdminLTE Skins. Choose a skin from the css/skins
        folder instead of downloading all of them to reduce the load. -->
  <link rel="stylesheet" href="../Plantilla/dist/css/skins/_all-skins.min.css">

  <!-- jvectormap -->
  <link rel="stylesheet" href="../Plantilla/bower_components/jvectormap/jquery-jvectormap.css">
  <!-- Date Picker -->
  <link rel="stylesheet" href="../Plantilla/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="../Plantilla/bower_components/bootstrap-daterangepicker/daterangepicker.css">
  <!-- bootstrap wysihtml5 - text editor -->
  <link rel="stylesheet" href="../Plantilla/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
  <!-- Tablas reportes -->
  <link rel="stylesheet" href="../Plantilla/plugins/DataTablesNew/datatables.min.css">

  <link rel="stylesheet" href="../Plantilla/plugins/select2/select2.min.css">

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

  <!-- Google Font -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
</head>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  <header class="main-header"> 
    <!-- Logo --> 
    <a href="index.php" class="logo"> 
    <!-- mini logo for sidebar mini 50x50 pixels --> 
    <span class="logo-mini"><b>S</b>GT</span> 
    <!-- logo for regular state and mobile devices --> 
    <span class="logo-lg"><b>Admin_</b>SGT</span> </a> 
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top"> 
      <!-- Sidebar toggle button--> 
      <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button"> <span class="sr-only">Toggle navigation</span> </a>
      <div class="navbar-custom-menu">
        <ul class="nav navbar-nav">

          <!-- Messages: style can be found in dropdown.less-->
          <!--
          <li class="dropdown messages-menu"> <a href="#" class="dropdown-toggle" data-toggle="dropdown"> <i class="fa fa-envelope-o"></i> <span class="label label-success">4</span> </a>
            <ul class="dropdown-menu">
              <li class="header">You have 4 messages</li>
              <li> 
                
                <ul class="menu">
                  <li> 
                    
                    <a href="#">
                    <div class="pull-left"> <img src="../Plantilla/dist/img/user2-160x160.jpg" class="img-circle" alt="User Image"> </div>
                    <h4> Support Team <small><i class="fa fa-clock-o"></i> 5 mins</small> </h4>
                    <p>Why not buy a new awesome theme?</p>
                    </a> </li>
                  
                  <li> <a href="#">
                    <div class="pull-left"> <img src="../Plantilla/dist/img/user3-128x128.jpg" class="img-circle" alt="User Image"> </div>
                    <h4> AdminLTE Design Team <small><i class="fa fa-clock-o"></i> 2 hours</small> </h4>
                    <p>Why not buy a new awesome theme?</p>
                    </a> </li>
                  <li> <a href="#">
                    <div class="pull-left"> <img src="../Plantilla/dist/img/user4-128x128.jpg" class="img-circle" alt="User Image"> </div>
                    <h4> Developers <small><i class="fa fa-clock-o"></i> Today</small> </h4>
                    <p>Why not buy a new awesome theme?</p>
                    </a> </li>
                  <li> <a href="#">
                    <div class="pull-left"> <img src="../Plantilla/dist/img/user3-128x128.jpg" class="img-circle" alt="User Image"> </div>
                    <h4> Sales Department <small><i class="fa fa-clock-o"></i> Yesterday</small> </h4>
                    <p>Why not buy a new awesome theme?</p>
                    </a> </li>
                  <li> <a href="#">
                    <div class="pull-left"> <img src="../Plantilla/dist/img/user4-128x128.jpg" class="img-circle" alt="User Image"> </div>
                    <h4> Reviewers <small><i class="fa fa-clock-o"></i> 2 days</small> </h4>
                    <p>Why not buy a new awesome theme?</p>
                    </a> </li>
                </ul>
              </li>
              <li class="footer"><a href="#">See All Messages</a></li>
            </ul>
          </li>
          -->

          <!-- Notifications: style can be found in dropdown.less -->
          <!--
          <li class="dropdown notifications-menu"> <a href="#" class="dropdown-toggle" data-toggle="dropdown"> <i class="fa fa-bell-o"></i> <span class="label label-warning">10</span> </a>
            <ul class="dropdown-menu">
              <li class="header">You have 10 notifications</li>
              <li> 
                
                <ul class="menu">
                  <li> <a href="#"> <i class="fa fa-users text-aqua"></i> 5 new members joined today </a> </li>
                  <li> <a href="#"> <i class="fa fa-warning text-yellow"></i> Very long description here that may not fit into the
                    page and may cause design problems </a> </li>
                  <li> <a href="#"> <i class="fa fa-users text-red"></i> 5 new members joined </a> </li>
                  <li> <a href="#"> <i class="fa fa-shopping-cart text-green"></i> 25 sales made </a> </li>
                  <li> <a href="#"> <i class="fa fa-user text-red"></i> You changed your username </a> </li>
                </ul>
              </li>
              <li class="footer"><a href="#">View all</a></li>
            </ul>
          </li>
          -->

          <!--
          <li class="dropdown tasks-menu"> <a href="#" class="dropdown-toggle" data-toggle="dropdown"> <i class="fa fa-flag-o"></i> <span class="label label-danger">9</span> </a>
            <ul class="dropdown-menu">
              <li class="header">You have 9 tasks</li>
              <li> 
                
                <ul class="menu">
                  <li> 
                    
                    <a href="#">
                    <h3> Design some buttons <small class="pull-right">20%</small> </h3>
                    <div class="progress xs">
                      <div class="progress-bar progress-bar-aqua" style="width: 20%" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100"> <span class="sr-only">20% Complete</span> </div>
                    </div>
                    </a> </li>
                  
                  <li> 
                    
                    <a href="#">
                    <h3> Create a nice theme <small class="pull-right">40%</small> </h3>
                    <div class="progress xs">
                      <div class="progress-bar progress-bar-green" style="width: 40%" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100"> <span class="sr-only">40% Complete</span> </div>
                    </div>
                    </a> </li>
                  
                  <li> 
                     
                    <a href="#">
                    <h3> Some task I need to do <small class="pull-right">60%</small> </h3>
                    <div class="progress xs">
                      <div class="progress-bar progress-bar-red" style="width: 60%" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100"> <span class="sr-only">60% Complete</span> </div>
                    </div>
                    </a> </li>
                  
                  <li> 
                    
                    <a href="#">
                    <h3> Make beautiful transitions <small class="pull-right">80%</small> </h3>
                    <div class="progress xs">
                      <div class="progress-bar progress-bar-yellow" style="width: 80%" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100"> <span class="sr-only">80% Complete</span> </div>
                    </div>
                    </a> </li>
                  
                </ul>
              </li>
              <li class="footer"> <a href="#">View all tasks</a> </li>
            </ul>
          </li>
          -->

          <!-- User Account: style can be found in dropdown.less -->
          <li class="dropdown user user-menu"> <a href="#" class="dropdown-toggle" data-toggle="dropdown"> <img src="../Plantilla/dist/img/avatarLogo.jpg" class="user-image" alt="User Image"> <span class="hidden-xs"><?php echo $nombreUsuario;?></span> </a>
            <ul class="dropdown-menu">
              <!-- User image -->
              <li class="user-header"> <img src="../Plantilla/dist/img/avatarLogo.jpg" class="img-circle" alt="User Image">
                <p> <?php echo $nombreCompleto;?></p>
              </li>
              <!-- 
              <li class="user-body">
                <div class="row">
                  <div class="col-xs-4 text-center"> <a href="#">Followers</a> </div>
                  <div class="col-xs-4 text-center"> <a href="#">Sales</a> </div>
                  <div class="col-xs-4 text-center"> <a href="#">Friends</a> </div>
                </div>
                
              </li>
              -->
              <!-- Menu Footer-->
              <li class="user-footer">
                <!--
                <div class="pull-left"> <a href="#" class="btn btn-default btn-flat">Profile</a> </div>
                -->
                <div class="pull-right"> <a href="../controlador/usuario/controlador_cerrar_session.php" class="btn btn-default btn-flat">Salir</a> </div>
              </li>
            </ul>
          </li>
          <!-- Control Sidebar Toggle Button -->
          <li> <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a> </li>
        </ul>
      </div>
    </nav>
  </header>
  <!-- Left side column. contains the logo and sidebar -->
  <aside class="main-sidebar"> 
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar"> 
      <!-- Sidebar user panel -->
      <div class="user-panel">
        <div class="pull-left image"> <img src="../Plantilla/dist/img/avatarLogo.jpg" class="img-circle" alt="User Image"> </div>
        <div class="pull-left info">
          <p><?php echo $nombreUsuario;?></p>
          <a href="#"><i class="fa fa-circle text-success"></i> Online</a> </div>
      </div>
      <!-- search form -->
      <form action="#" method="get" class="sidebar-form">
        <div class="input-group">
          <input type="text" name="q" class="form-control" placeholder="Search...">
          <span class="input-group-btn">
          <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i> </button>
          </span> </div>
      </form>
      <!-- /.search form --> 
      <!-- sidebar menu: : style can be found in sidebar.less -->
      
      <ul class="sidebar-menu" data-widget="tree">
        <li class="header">MENU DE NAVEGACION</li>

        <li class="treeview"> <a href="#"> <i class="fa fa-files-o"></i> <span>Catalogo</span> <span class="pull-right-container"> <span class="label label-primary pull-right">2</span> </span> </a>
          <ul class="treeview-menu">
            <!--
            <li ><a href="#"><i class="fa fa-circle-o"></i> Clientes</a></li>
            <li><a href="#"><i class="fa fa-circle-o"></i> Roles</a></li>
            -->
            <li class="active"> <a href="#" onclick="cargar_contenido('contenido_principal','usuario/tblUsuario.php')"><i class="fa fa-circle-o"></i> Usuarios</a> </li>
            <li class="active"> <a href="#" onclick="cargar_contenido('contenido_principal','areas/tblAreas.php')"><i class="fa fa-circle-o"></i> Areas</a> </li>
          </ul>
        </li>

        <li class="treeview"> <a href="#"> <i class="fa fa-files-o"></i> <span>Operación</span> <span class="pull-right-container"> <span class="label label-primary pull-right">4</span> </span> </a>
          <ul class="treeview-menu">
            <li><a href="#" onclick="cargar_contenido('contenido_principal','contenido/tblAvisos.php')"><i class="fa fa-circle-o"></i> Avisos</a></li>
            <li><a href="#" onclick="cargar_contenido('contenido_principal','contenido/tblEventos.php')"><i class="fa fa-circle-o"></i> Eventos</a></li>
            <li><a href="#" onclick="cargar_contenido('contenido_principal','contenido/tblNoticias.php')"><i class="fa fa-circle-o"></i> Noticias</a></li>
            <li><a href="#" onclick="cargar_contenido('contenido_principal','contenido/tblGaleria.php')"><i class="fa fa-circle-o"></i> Galeria</a></li>
          </ul>
        </li>
        <!--
        <li> <a href="../Plantilla/pages/widgets.html"> <i class="fa fa-th"></i> <span>Widgets</span> <span class="pull-right-container"> <small class="label pull-right bg-green">new</small> </span> </a> </li>
        <li class="treeview"> <a href="#"> <i class="fa fa-pie-chart"></i> <span>Charts</span> <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
          <ul class="treeview-menu">
            <li><a href="../Plantilla/pages/charts/chartjs.html"><i class="fa fa-circle-o"></i> ChartJS</a></li>
            <li><a href="../Plantilla/pages/charts/flot.html"><i class="fa fa-circle-o"></i> Flot</a></li>
            <li><a href="../Plantilla/pages/charts/inline.html"><i class="fa fa-circle-o"></i> Inline charts</a></li>
          </ul>
        </li>
        <li class="treeview"> <a href="#"> <i class="fa fa-laptop"></i> <span>UI Elements</span> <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
          <ul class="treeview-menu">
            <li><a href="../Plantilla/pages/UI/general.html"><i class="fa fa-circle-o"></i> General</a></li>
            <li><a href="../Plantilla/pages/UI/icons.html"><i class="fa fa-circle-o"></i> Icons</a></li>
            <li><a href="../Plantilla/pages/UI/buttons.html"><i class="fa fa-circle-o"></i> Buttons</a></li>
            <li><a href="../Plantilla/pages/UI/sliders.html"><i class="fa fa-circle-o"></i> Sliders</a></li>
            <li><a href="../Plantilla/pages/UI/timeline.html"><i class="fa fa-circle-o"></i> Timeline</a></li>
            <li><a href="../Plantilla/pages/UI/modals.html"><i class="fa fa-circle-o"></i> Modals</a></li>
          </ul>
        </li>
        <li class="treeview"> <a href="#"> <i class="fa fa-edit"></i> <span>Forms</span> <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
          <ul class="treeview-menu">
            <li><a href="../Plantilla/pages/forms/general.html"><i class="fa fa-circle-o"></i> General Elements</a></li>
            <li><a href="../Plantilla/pages/forms/advanced.html"><i class="fa fa-circle-o"></i> Advanced Elements</a></li>
            <li><a href="../Plantilla/pages/forms/editors.html"><i class="fa fa-circle-o"></i> Editors</a></li>
          </ul>
        </li>
        <li class="treeview"> <a href="#"> <i class="fa fa-table"></i> <span>Tables</span> <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
          <ul class="treeview-menu">
            <li><a href="../Plantilla/pages/tables/simple.html"><i class="fa fa-circle-o"></i> Simple tables</a></li>
            <li><a href="../Plantilla/pages/tables/data.html"><i class="fa fa-circle-o"></i> Data tables</a></li>
          </ul>
        </li>
        <li> <a href="../Plantilla/pages/calendar.html"> <i class="fa fa-calendar"></i> <span>Calendar</span> <span class="pull-right-container"> <small class="label pull-right bg-red">3</small> <small class="label pull-right bg-blue">17</small> </span> </a> </li>
        <li> <a href="../Plantilla/pages/mailbox/mailbox.html"> <i class="fa fa-envelope"></i> <span>Mailbox</span> <span class="pull-right-container"> <small class="label pull-right bg-yellow">12</small> <small class="label pull-right bg-green">16</small> <small class="label pull-right bg-red">5</small> </span> </a> </li>
        <li class="treeview"> <a href="#"> <i class="fa fa-folder"></i> <span>Examples</span> <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
          <ul class="treeview-menu">
            <li><a href="../Plantilla/pages/examples/invoice.html"><i class="fa fa-circle-o"></i> Invoice</a></li>
            <li><a href="../Plantilla/pages/examples/profile.html"><i class="fa fa-circle-o"></i> Profile</a></li>
            <li><a href="../Plantilla/pages/examples/login.html"><i class="fa fa-circle-o"></i> Login</a></li>
            <li><a href="../Plantilla/pages/examples/register.html"><i class="fa fa-circle-o"></i> Register</a></li>
            <li><a href="../Plantilla/pages/examples/lockscreen.html"><i class="fa fa-circle-o"></i> Lockscreen</a></li>
            <li><a href="../Plantilla/pages/examples/404.html"><i class="fa fa-circle-o"></i> 404 Error</a></li>
            <li><a href="../Plantilla/pages/examples/500.html"><i class="fa fa-circle-o"></i> 500 Error</a></li>
            <li><a href="../Plantilla/pages/examples/blank.html"><i class="fa fa-circle-o"></i> Blank Page</a></li>
            <li><a href="../Plantilla/pages/examples/pace.html"><i class="fa fa-circle-o"></i> Pace Page</a></li>
          </ul>
        </li>
        <li class="treeview"> <a href="#"> <i class="fa fa-share"></i> <span>Multilevel</span> <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
          <ul class="treeview-menu">
            <li><a href="#"><i class="fa fa-circle-o"></i> Level One</a></li>
            <li class="treeview"> <a href="#"><i class="fa fa-circle-o"></i> Level One <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
              <ul class="treeview-menu">
                <li><a href="#"><i class="fa fa-circle-o"></i> Level Two</a></li>
                <li class="treeview"> <a href="#"><i class="fa fa-circle-o"></i> Level Two <span class="pull-right-container"> <i class="fa fa-angle-left pull-right"></i> </span> </a>
                  <ul class="treeview-menu">
                    <li><a href="#"><i class="fa fa-circle-o"></i> Level Three</a></li>
                    <li><a href="#"><i class="fa fa-circle-o"></i> Level Three</a></li>
                  </ul>
                </li>
              </ul>
            </li>
            <li><a href="#"><i class="fa fa-circle-o"></i> Level One</a></li>
          </ul>
        </li>
        <li><a href="https://adminlte.io/docs"><i class="fa fa-book"></i> <span>Documentation</span></a></li>
        <li class="header">LABELS</li>
        <li><a href="#"><i class="fa fa-circle-o text-red"></i> <span>Important</span></a></li>
        <li><a href="#"><i class="fa fa-circle-o text-yellow"></i> <span>Warning</span></a></li>
        <li><a href="#"><i class="fa fa-circle-o text-aqua"></i> <span>Information</span></a></li>
        -->
      </ul>

    </section>

    <!-- /.sidebar --> 
  </aside>
  
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper"> 
    
    <!-- Main content -->
    <section class="content">
      <div class="row" id="contenido_principal">
        <div class="col-md-12">
          <div class="box box-warning box-solid">
            <div class="box-header with-border">
              <h3 class="box-title">BIENVENIDO AL CONTENIDO PRINCIPAL</h3>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i> </button>
              </div>
              <!-- /.box-tools --> 
            </div>
            <!-- /.box-header -->
            <div class="box-body"> CONTENIDO PRINCIPAL </div>
            <!-- /.box-body --> 
          </div>
          <!-- /.box --> 
        </div>
      </div>
    </section>
    <!-- /.content --> 
  </div>

  <!-- /.content-wrapper -->
  <footer class="main-footer">
    <div class="pull-right hidden-xs"> <b>Version</b> 2.4.13 </div>
    <strong>Copyright &copy; 2022 <a href="https://sgtecno.com">SGTecno</a>.</strong> All rights
    reserved. 
  </footer>
  
    <!-- Control Sidebar -->
    <!--
  <aside class="control-sidebar control-sidebar-dark" style="display: none;"> 
    
    <ul class="nav nav-tabs nav-justified control-sidebar-tabs">
      <li><a href="#control-sidebar-home-tab" data-toggle="tab"><i class="fa fa-home"></i></a></li>
      <li><a href="#control-sidebar-settings-tab" data-toggle="tab"><i class="fa fa-gears"></i></a></li>
    </ul>
    
    <div class="tab-content"> 
      
      <div class="tab-pane" id="control-sidebar-home-tab">
        <h3 class="control-sidebar-heading">Recent Activity</h3>
        <ul class="control-sidebar-menu">
          <li> <a href="javascript:void(0)"> <i class="menu-icon fa fa-birthday-cake bg-red"></i>
            <div class="menu-info">
              <h4 class="control-sidebar-subheading">Langdon's Birthday</h4>
              <p>Will be 23 on April 24th</p>
            </div>
            </a> </li>
          <li> <a href="javascript:void(0)"> <i class="menu-icon fa fa-user bg-yellow"></i>
            <div class="menu-info">
              <h4 class="control-sidebar-subheading">Frodo Updated His Profile</h4>
              <p>New phone +1(800)555-1234</p>
            </div>
            </a> </li>
          <li> <a href="javascript:void(0)"> <i class="menu-icon fa fa-envelope-o bg-light-blue"></i>
            <div class="menu-info">
              <h4 class="control-sidebar-subheading">Nora Joined Mailing List</h4>
              <p>nora@example.com</p>
            </div>
            </a> </li>
          <li> <a href="javascript:void(0)"> <i class="menu-icon fa fa-file-code-o bg-green"></i>
            <div class="menu-info">
              <h4 class="control-sidebar-subheading">Cron Job 254 Executed</h4>
              <p>Execution time 5 seconds</p>
            </div>
            </a> </li>
        </ul>
        
        
        <h3 class="control-sidebar-heading">Tasks Progress</h3>
        <ul class="control-sidebar-menu">
          <li> <a href="javascript:void(0)">
            <h4 class="control-sidebar-subheading"> Custom Template Design <span class="label label-danger pull-right">70%</span> </h4>
            <div class="progress progress-xxs">
              <div class="progress-bar progress-bar-danger" style="width: 70%"></div>
            </div>
            </a> </li>
          <li> <a href="javascript:void(0)">
            <h4 class="control-sidebar-subheading"> Update Resume <span class="label label-success pull-right">95%</span> </h4>
            <div class="progress progress-xxs">
              <div class="progress-bar progress-bar-success" style="width: 95%"></div>
            </div>
            </a> </li>
          <li> <a href="javascript:void(0)">
            <h4 class="control-sidebar-subheading"> Laravel Integration <span class="label label-warning pull-right">50%</span> </h4>
            <div class="progress progress-xxs">
              <div class="progress-bar progress-bar-warning" style="width: 50%"></div>
            </div>
            </a> </li>
          <li> <a href="javascript:void(0)">
            <h4 class="control-sidebar-subheading"> Back End Framework <span class="label label-primary pull-right">68%</span> </h4>
            <div class="progress progress-xxs">
              <div class="progress-bar progress-bar-primary" style="width: 68%"></div>
            </div>
            </a> </li>
        </ul>
       
        
      </div>
      <
      <div class="tab-pane" id="control-sidebar-stats-tab">Stats Tab Content</div>
      <
      <div class="tab-pane" id="control-sidebar-settings-tab">
        <form method="post">
          <h3 class="control-sidebar-heading">General Settings</h3>
          <div class="form-group">
            <label class="control-sidebar-subheading"> Report panel usage
              <input type="checkbox" class="pull-right" checked>
            </label>
            <p> Some information about this general settings option </p>
          </div>
          
          
          <div class="form-group">
            <label class="control-sidebar-subheading"> Allow mail redirect
              <input type="checkbox" class="pull-right" checked>
            </label>
            <p> Other sets of options are available </p>
          </div>
          
          
          <div class="form-group">
            <label class="control-sidebar-subheading"> Expose author name in posts
              <input type="checkbox" class="pull-right" checked>
            </label>
            <p> Allow the user to show his name in blog posts </p>
          </div>
          
          <h3 class="control-sidebar-heading">Chat Settings</h3>
          <div class="form-group">
            <label class="control-sidebar-subheading"> Show me as online
              <input type="checkbox" class="pull-right" checked>
            </label>
          </div>
          
          
          <div class="form-group">
            <label class="control-sidebar-subheading"> Turn off notifications
              <input type="checkbox" class="pull-right">
            </label>
          </div>
          
          
          <div class="form-group">
            <label class="control-sidebar-subheading"> Delete chat history <a href="javascript:void(0)" class="text-red pull-right"><i class="fa fa-trash-o"></i></a> </label>
          </div>
          
        </form>
      </div>
      
    </div>
  </aside>
  -->
    
  <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper --> 

    <!-- jQuery 3 --> 
    <script src="../Plantilla/bower_components/jquery/dist/jquery.min.js"></script> 
    <!-- jQuery UI 1.11.4 --> 
    <script src="../Plantilla/bower_components/jquery-ui/jquery-ui.min.js"></script> 
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip --> 
    <script>
        var idioma_espanol = {
          select: {
            rows: "%d fila seleccionada"
            },
            "sProcessing":     "Procesando...",
            "sLengthMenu":     "Mostrar _MENU_ registros",
            "sZeroRecords":    "No se encontraron resultados",
            "sEmptyTable":     "Ning&uacute;n dato disponible en esta tabla",
            "sInfo":           "Registros del (_START_ al _END_) total de _TOTAL_ registros",
            "sInfoEmpty":      "Registros del (0 al 0) total de 0 registros",
            "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
            "sInfoPostFix":    "",
            "sSearch":         "Buscar:",
            "sUrl":            "",
            "sInfoThousands":  ",",
            "sLoadingRecords": "<b>No se encontraron datos</b>",
            "oPaginate": {
                "sFirst":    "Primero",
                "sLast":     "Último",
                "sNext":     "Siguiente",
                "sPrevious": "Anterior"
            },
            "oAria": {
                "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                "sSortDescending": ": Activar para ordenar la columna de manera descendente"
            }
          }

            function cargar_contenido(contenedor, contenido) {
                $("#" + contenedor).load(contenido);
            }

            $.widget.bridge('uibutton', $.ui.button);
    </script> 
    <!-- Bootstrap 3.3.7 --> 
    <script src="../Plantilla/bower_components/bootstrap/dist/js/bootstrap.min.js"></script> 
    <!-- Morris.js charts --> 
    <script src="../Plantilla/bower_components/raphael/raphael.min.js"></script> 

    <!-- Sparkline --> 
    <script src="../Plantilla/bower_components/jquery-sparkline/dist/jquery.sparkline.min.js"></script> 
    <!-- jvectormap --> 
    <script src="../Plantilla/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js"></script> 
    <script src="../Plantilla/plugins/jvectormap/jquery-jvectormap-world-mill-en.js"></script> 
    <!-- jQuery Knob Chart --> 
    <script src="../Plantilla/bower_components/jquery-knob/dist/jquery.knob.min.js"></script> 
    <!-- daterangepicker --> 
    <script src="../Plantilla/bower_components/moment/min/moment.min.js"></script> 
    <script src="../Plantilla/bower_components/bootstrap-daterangepicker/daterangepicker.js"></script> 
    <!-- datepicker --> 
    <script src="../Plantilla/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script> 
    <!-- Bootstrap WYSIHTML5 --> 
    <script src="../Plantilla/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script> 
    <!-- Slimscroll --> 
    <script src="../Plantilla/bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script> 
    <!-- FastClick --> 
    <script src="../Plantilla/bower_components/fastclick/lib/fastclick.js"></script> 
    <!-- AdminLTE App --> 
    <script src="../Plantilla/dist/js/adminlte.min.js"></script> 
    <!-- AdminLTE dashboard demo (This is only for demo purposes) --> 
    <script src="../Plantilla/dist/js/pages/dashboard.js"></script> 
    <!-- AdminLTE for demo purposes --> 
    <script src="../Plantilla/dist/js/demo.js"></script>
    <!-- Tablas de reporte o vistas --> 
    <script src="../Plantilla/plugins/DataTablesNew/datatables.min.js"></script>

    <script src="../Plantilla/plugins//select2/select2.min.js"></script>

</body>
</html>