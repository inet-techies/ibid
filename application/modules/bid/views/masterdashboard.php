
                <div class="row">
                    <!-- [Bidwise Records] start -->
               <?php foreach($statusCounts as $statusKey => $row): 
                            $percentage = ($totalBids > 0) ? round(($row['Count'] / $totalBids) * 100) : 0;
                            $chartId = "chart_" . $statusKey;
                        ?>

                        <div class="col-lg-4">
                            <div class="card mb-4 stretch stretch-full">
                                
                                <div class="card-header d-flex align-items-center justify-content-between">
                                    
                                    <div class="d-flex gap-3 align-items-center">
                                        
                                        <div class="avatar-text <?= $row['BadgeClass'] ?? 'bg-secondary'; ?> text-white">
                                            <i class="<?= $row['IconClass'] ?? 'feather-file'; ?>"></i>
                                        </div>
                                        
                                        <div>
                                            <div class="fw-semibold text-dark">
                                                Bids <?= $row['DisplayName']; ?>
                                            </div>
                                          <!--   <div class="fs-12 text-muted">
                                                <?= $row['Count']; ?>/<?= $totalBids; ?> completed
                                            </div> -->
                                        </div>

                                    </div>

                                    <!-- 🔹 Redirect Icon Added Here -->
                                    <div class="d-flex align-items-center gap-3">
                                        
                                        <div class="fs-4 fw-bold text-dark">
                                            <?= $row['Count']; ?>/<?= $totalBids; ?>
                                        </div>

                                        <a href="<?= base_url('bid/'.$statusKey); ?>" 
                                           class="text-muted"
                                           title="View <?= $row['DisplayName']; ?>">
                                            <i class="feather-eye"></i>
                                        </a>

                                    </div>

                                </div>

                                <div class="card-body d-flex align-items-center justify-content-between gap-4">
                                    
                                    <div id="<?= $chartId; ?>"></div>

                                    <div class="fs-12 text-muted text-nowrap">
                                        <span class="fw-semibold text-primary">
                                            <?= $percentage; ?>%
                                        </span><br />
                                        <span>of total</span>
                                    </div>

                                </div>

                            </div>
                        </div>

                        <?php endforeach; ?>
                   
 
           
                    <!-- [Latest Leads] start -->
                    <div class="col-xxl-12">
                        <div class="card stretch stretch-full">
                            <div class="card-header">
                                <h5 class="card-title">Recent Bids</h5>
                                <div class="card-header-action">
                                    <div class="card-header-btn"> 
                                        <div data-bs-toggle="tooltip" title="Refresh">
                                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-warning" data-bs-toggle="refresh"> </a>
                                        </div>
                                        <div data-bs-toggle="tooltip" title="Maximize/Minimize">
                                            <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success" data-bs-toggle="expand"> </a>
                                        </div>
                                    </div> 
                                </div>
                            </div>
                            <div class="card-body custom-card-action p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr class="border-b">
                                                <th scope="row">Bid Title</th>
                                                <th>Bid Type</th>
                                                <th>Department</th>
                                                <th>Published Date</th>
                                                <th>Start Date</th>
                                                <th>Submission Date</th>
                                                <th>Status</th>
                                                <th>Creation Date</th>
                                                <th>Remarks</th>
                                             </tr>
                                        </thead>
                                        <tbody>

                                            <?php if(!empty($lastBids)): ?>

                                            <?php foreach($lastBids as $bid): ?>

                                            <tr>

                                                <!-- Bid Title -->
                                                <td>
                                                    <div class="d-flex align-items-center gap-3">
                                                         <span class="d-block"><?= $bid['BidTitle']; ?></span> 
                                                    </div>
                                                </td>

                                                <!-- Bid Type -->
                                                <td>
                                                    <?php if(strtolower($bid['BidType']) == 'oem'): ?>
                                                        <span class="badge bg-soft-info text-info">OEM</span>
                                                    <?php elseif(strtolower($bid['BidType']) == 'service'): ?>
                                                        <span class="badge bg-soft-primary text-primary">Service</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-soft-danger text-danger"><?= $bid['BidType']; ?></span>
                                                    <?php endif; ?>
                                                </td>

                                                <!-- Department -->
                                                <td><?= $bid['BidDepartment']; ?></td>

                                                <!-- Published Date -->
                                                <td><?= date('d-m-Y', strtotime($bid['PublishedDate'])); ?></td>
                                                <td><?= date('d-m-Y', strtotime($bid['OpeningDate'])); ?></td>
                                                <td><?= date('d-m-Y', strtotime($bid['SubmissionDate'])); ?></td>

                                                <!-- Status -->
                                                <td>
                                                    <span class="badge <?= $bid['BadgeClass'] ?? 'bg-secondary'; ?>">
                                                        <?= $bid['StatusName']; ?>
                                                    </span>
                                                </td>
                                                 <td><?= date('d-m-Y H:i:s', strtotime($bid['CreationTs'])); ?> </td>
                                                 <td>
                                                     <span class="d-inline-block text-truncate" style="max-width: 180px;" title="<?= htmlspecialchars($bid['Remarks'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                         <?= !empty($bid['Remarks']) ? htmlspecialchars($bid['Remarks'], ENT_QUOTES, 'UTF-8') : '-'; ?>
                                                     </span>
                                                 </td>
                                            </tr>

                                            <?php endforeach; ?>

                                            <?php else: ?>

                                            <tr>
                                                <td colspan="9" class="text-center text-muted">
                                                    No bids found
                                                </td>
                                            </tr>

                                            <?php endif; ?>

                                            </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer text-end">
                                <div class="d-flex justify-content-end gap-2 mt-3"> 
                                     <a href="<?= base_url('bid/BidDetails'); ?>">
                                        <button type="submit" class="btn btn-sm btn-primary"> 
                                        View All Bids 
                                    </button> </a>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    <!-- [Latest Leads] end -->
                    
                </div>
            </div>

<script>
document.addEventListener("DOMContentLoaded", function () {

<?php 
$chartTypes = ['area', 'bar', 'line']; 
$index = 0;

foreach($statusCounts as $statusKey => $row): 

    $chartId = "chart_" . $statusKey;
    $percentage = ($totalBids > 0) ? round(($row['Count'] / $totalBids) * 100) : 0;
    $color = $colorMap[$row['BadgeClass']] ?? '#6c757d';
    $chartType = $chartTypes[$index % count($chartTypes)];
?>

new ApexCharts(document.querySelector("#<?= $chartId; ?>"), {
    chart: {
        type: '<?= $chartType; ?>',
        height: 70,
        sparkline: { enabled: true }
    },
    series: [{
        data: [0, <?= $percentage/2 ?>, <?= $percentage ?>]
    }],
    stroke: {
        curve: 'smooth',
        width: 2
    },
    plotOptions: {
        bar: {
            columnWidth: '40%',
            borderRadius: 3
        }
    },
    fill: {
        opacity: 0.3
    },
    colors: ['<?= $color; ?>'],
    tooltip: {
        y: {
            formatter: function(val) {
                return val + "%";
            }
        }
    }
}).render();

<?php 
$index++; 
endforeach; 
?>

});
</script>