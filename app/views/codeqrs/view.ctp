<div class="c-data">
        <div class="box-element">
                <div class="box-head">QR Codes of Project:<?php echo " ".$projectName; ?><a href="" class="collapsable"></a></div>
                <div class="box-content no-padding grey-bg">
                        <table cellpadding="0" cellspacing="0" border="0" class="display" id="datatable">
                                <thead>
                                        <tr>
                                                <th>Serial Number</th>
                                                <th>Location</th>
                                                <th>Download</th>
                                        </tr>
                                </thead>
                                <tbody>
                                     <?php
                                     if($qrCodes && is_array($qrCodes)){
                                     foreach($qrCodes as $qrCode){ ?>
                                        <tr>
                                                <td><?php echo $qrCode['Codeqr']['serialNo'] ?></td>
                                                <td><?php echo $qrCode['Codeqr']['location'] ?></td>
                                                <td><a href="/img/qrcodes/<?php echo $qrCode['Codeqr']['imagePath'] ?>" class="i-link">Download</a></td>
                                                
                                        </tr>
                                        <?php } 
                                        
                                        }else{ ?>
                                           <tr> <td>There is no QrCodes</td> </tr>
                                       <?php }?>
                                </tbody>
                                <tfoot>
                                        <tr>
                                                <th>Serial Number</th>
                                                <th>Location</th>
                                                <th>Download</th>
                                        </tr>
                                </tfoot>
                        </table>
                        <div class="clearfix"></div>
                </div>
        </div><div><a href="/codeqrs/add/<?php echo $idProject ?>" class="i-button-add">Add QR Code</a></div>
</div>