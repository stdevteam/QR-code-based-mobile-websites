<div class="c-data">
        <div class="box-element">
                <div class="box-head">Projects <a href="" class="collapsable"></a></div>
                <div class="box-content no-padding grey-bg">
                        <table cellpadding="0" cellspacing="0" border="0" class="display" id="datatable">
                                <thead>
                                        <tr>
                                                <th>Name</th>
                                                <th>Description</th>
                                                <th>Edit Project</th>
                                                <th>QR List</th>
                                                <th>Add QR Code</th>
                                                <th>Mobile Site</th>
                                        </tr>
                                </thead>
                                <tbody>
                                     <?php
                                     if($projects && is_array($projects)){
                                        foreach($projects as $project){ ?>
                                        <tr>
                                                <td><a href="/projects/edit/<?php echo $project['Project']['id'] ?>" class="i-link">
                                                    <?php echo $project['Project']['name'] ?>
                                                </a></td>
                                                <td>
                                                    <?php echo $project['Project']['description'] ?>
                                                </td>
                                                <td><a href="/projects/edit/<?php echo $project['Project']['id'] ?>" class="i-link">Edit Project</a></td>
                                                <td class="center"> <a href="/codeqrs/view/<?php echo $project['Project']['id'] ?>" class="i-link">Show List</a></td>
                                                <td class="center"><a href="/codeqrs/add/<?php echo $project['Project']['id'] ?>" class="i-link">Add</a></td>
                                                <td class="center"><a href="/projects/mobile/<?php echo $project['Project']['id'] ?>" class="i-link">Mobile Site</a></td>
                                        </tr>
                                        <?php } 
                                        
                                        }else{ ?>
                                            <td>There is no Projects</td>
                                       <?php }?>
                                </tbody>
                                <tfoot>
                                        <tr>
                                                <th>Name</th>
                                                <th>Description</th>
                                                <th>Edit Project</th>
                                                <th>QR List</th>
                                                <th>Add QR Code</th>
                                                <th>Mobile Site</th>
                                        </tr>
                                </tfoot>
                        </table>
                        <div class="clearfix"></div>
                        
                </div><div class="clearfix"></div>
        </div><div><a href="/projects/add/" class="i-button-add">Add Project</a></div>
</div>
