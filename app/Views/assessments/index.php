<?= $this->extend("layout") ?>
<?= $this->section("content") ?>

<div class="row">
    <div class="col-12 grid-margin">
        <div class="card" style="border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Manage Assessments</h4>
                    <div>
                        <button class="btn btn-sm btn-primary text-white me-2" onclick="window.location.href='/api/assessment/export'" style="background-color: #e75c25; border-color: #e75c25; padding: 6px 15px; font-size: 13px; border-radius: 4px;">
                            <i class="mdi mdi-file-export"></i> Export
                        </button>
                        <a href="/assessment/create" class="btn btn-sm btn-primary text-white" style="background-color: #e75c25; border-color: #e75c25; padding: 6px 15px; font-size: 13px; border-radius: 4px;">
                            <i class="mdi mdi-plus"></i> Add Assessment
                        </a>
                    </div>
                </div>

                <div class="table-responsive">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="dataTables_length">
                            <label>Show 
                                <select class="form-select form-select-sm d-inline-block w-auto">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                </select> entries
                            </label>
                        </div>
                        <div class="dataTables_filter">
                            <input type="search" class="form-control form-control-sm" placeholder="Search">
                        </div>
                    </div>

                    <table class="table table-striped table-hover" id="assessmentTable">
                        <thead class="bg-black text-white">
                            <tr>
                                <th class="text-white">CANDIDATE <i class="mdi mdi-swap-vertical text-muted"></i></th>
                                <th class="text-white">JOB TITLE <i class="mdi mdi-swap-vertical text-muted"></i></th>
                                <th class="text-white">ROUND <i class="mdi mdi-swap-vertical text-muted"></i></th>
                                <th class="text-white">SCORE <i class="mdi mdi-swap-vertical text-muted"></i></th>
                                <th class="text-white">ACTION <i class="mdi mdi-swap-vertical text-muted"></i></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($assessments)): foreach($assessments as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)($item['candidate_name'] ?? 'Unknown')) ?></td>
                                <td><?= htmlspecialchars((string)($item['job_title'] ?? '')) ?></td>
                                <td><?= htmlspecialchars((string)($item['interview_round'] ?? '')) ?></td>
                                <td>
                                    <span class="badge" style="background-color: #e75c25;"><?= htmlspecialchars((string)($item['overall_score'] ?? '0')) ?> / 5</span>
                                </td>
                                <td>
                                    <a href="/assessment/view/<?= $item['id'] ?>" class="text-primary me-2"><i class="mdi mdi-eye fs-5"></i></a>
                                    <a href="/assessment/edit/<?= $item['id'] ?>" class="text-warning me-2"><i class="mdi mdi-pencil fs-5"></i></a>
                                    <a href="/assessment/delete/<?= $item['id'] ?>" class="text-danger" onclick="return confirm('Are you sure you want to delete this assessment?');"><i class="mdi mdi-delete fs-5"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">No assessments found.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="dataTables_info text-muted">
                            Showing 1 to 1 of 1 entries
                        </div>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                            <li class="page-item active"><a class="page-link" href="#" style="background-color: #e75c25; border-color: #e75c25;">1</a></li>
                            <li class="page-item disabled"><a class="page-link" href="#">Next</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
