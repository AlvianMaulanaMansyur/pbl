<div class="container-fluid px-2 mt-2 ">
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800 ">Data Pesanan</h1>
            </div>

            <?php echo form_open('Dashboard/search_pesanan', 'class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3  my-2 my-md-0"'); ?>
            <div class="input-group d-flex">
                <?php echo form_input('keyword', '', 'class="form-control" placeholder="Search for..." aria-label="Search for..."'); ?>
                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
            </div>
            <?php echo form_close(); ?>

            <?php if(empty($orders)): ?>
                <p class="pt-3">
                    pesanan tidak ditemukan!
                </p>
                <?php else : ?>
            <?php $no = 1; ?>
            <?php foreach ($orders as $invoice_number => $order) { ?>
                <div class="row d-flex">
                    <div class="col">
                        <h1>Pesanan <?php echo $invoice_number; ?></h1>
                        <h5>Nama : <?php echo $order['details'][0]['nama_customer']; ?></h5>
                        <h5>Alamat : <?php echo $order['details'][0]['alamat_pengiriman']; ?></h5>
                        <h5>Detail Alamat : <?php echo $order['details'][0]['detail_alamat_pengiriman']; ?></h5>
                        <h5>No. Telepon : <?php echo $order['details'][0]['telepon']; ?></h5>
                        <table class="table table-secondary">   
                            <thead>
                                <tr>
                                    <th scope="col">Nama Produk</th>
                                    <th scope="col">Harga</th>
                                    <th scope="col">Qty</th>
                                    <th scope="col">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="table-group-divider">
                                <?php foreach ($order['details'] as $detail) { ?>
                                    <tr>
                                        <td><?php echo $detail['nama_produk'] ?></td>
                                        <td class="format"><?php echo $detail['harga_produk'] ?></td>
                                        <td><?php echo $detail['qty_produk'] ?></td>
                                        <td class="format"><?php echo $detail['subtotal'] ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        <div class="d-flex flex-column" style="align-items: end;">
                            <h4 class="">Total : <span class="format"><?php echo $order['total'] ?></span></h4>
                            <a href="javascript:void(0);" class="btn <?php echo ($order['status'] == 0) ? 'btn-warning' : 'btn-success'; ?> col-2 <?php echo ($order['status'] == 1) ? 'disabled-link' : ''; ?>" style="display: flex; justify-content: center; align-items: center;" onclick="confirmUpdateOrder(<?php echo $order['id_pesanan']; ?>, <?php echo $order['status']; ?>)">
                                <h5>
                                    <?php
                                    $status = $order['status'];
                                    if ($status == 0) {
                                        echo 'Belum Lunas';
                                    } else {
                                        echo 'Sudah Lunas';
                                    }
                                    ?>
                                </h5>
                            </a>

                        </div>
                    </div>
                </div>
            <?php } ?>
            <?php endif ?>
        </div>
    </div>
</div>

<script>
    function confirmUpdateOrder(orderId, orderStatus) {
        // Pengecekan status pesanan
        if (orderStatus == 1) {
            Swal.fire({
                title: "Pesanan Sudah Lunas",
                text: "Pesanan ini sudah lunas dan tidak dapat diubah lagi.",
                icon: "info",
                confirmButtonColor: "#3085d6",
                confirmButtonText: "OK"
            });
            return;
        }

        // Konfirmasi untuk pesanan yang belum lunas
        Swal.fire({
            title: "Update Order?",
            text: "Apakah Anda yakin ingin mengupdate pesanan ini?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Ya, update!",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect ke halaman updateOrder jika konfirmasi diterima
                window.location.href = '<?php echo base_url("dashboard/updateOrder/"); ?>' + orderId;
            }
        });
    }
</script>