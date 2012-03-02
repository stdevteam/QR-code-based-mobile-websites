<?php
$pages = array(
    '/dashboard',
    '/projects', 
    '/codeqrs',
    '/analyitics'
    );
$active = null;
foreach($pages as $key => $item){
    if(strpos(strtolower($item), strtolower($this->here)) !== false){
        $active = $key;
    }
}
$controller = strtolower($this->params['controller']);
$action = strtolower($this->params['action']);
if(is_null($active)){
    if($controller == 'dashboard'){
        $active = 0;
    }
    if($controller == 'projects'){
        $active = 1;
    }
    if($controller == 'codeqrs'){
        $active = 2;
    }
    if($controller == 'analyitics'){
        $active = 3;
    }
}
?>

<div id="main-navigation">
    <ul>
        <li><a href="#dashboard" class="<?php echo ($active == 0)? 'active':''; ?>" id="dashboard-m"><span class="dashboard-32" title="Dashboard area">Dashboard</span></a></li>
        <li><a href="/projects/view" id="elements-m" class="<?php echo ($active == 1)? 'active':''; ?>"><span class="files-32" title="Projects">Projects</span></a></li>
        <?php if(isset($lastProject) && $lastProject != ''){ 
            if($lastProject != false){ ?>
        <li><a href="/codeqrs/view/<?php echo $lastProject ?>" id="forms-m" class="<?php echo ($active == 2 )? 'active':''; ?>"><span class="forms-32" title="QR Codes ">QR Codes</span></a></li>
        <?php 
            }else{ ?>
        <li><a href="/codeqrs/add/<?php echo $lastProject ?>" id="forms-m"><span class="forms-32" title="QR Codes " class="<?php echo ($active == 2 )? 'active':''; ?>">QR Codes</span></a></li>
          <?php  }
        } ?>
       <?php // <li><a href="#file" id="file-m"><span class="file-32" title="File manager area">File manager</span></a></li> ?>
        <li><a href="#analyitics" id="charts-m"><span class="charts-32" title="Analyitics " class="<?php echo ($active == 3)? 'active':''; ?>">Analyitics</span></a></li>
    </ul>
</div>