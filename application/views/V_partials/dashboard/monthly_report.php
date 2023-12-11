<!-- monthly_report.php -->
<div id="monthlyReportContainer">
    <div>
        <label for="month"></label>
        <input type="month" name="month" id="month" value="<?php echo $selected_month ?>" required onchange="updateMonthlyReport(this.value)">
    </div>
    <h2>Monthly Report <?php echo $formatMY ?></h2>

    <table class="table table-secondary">
        <thead>
            <tr>
                <th>Customer Name</th>
                <th>No Pesanan</th>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th>Date</th>

            </tr>
        </thead>
        <tbody>
            <?php $total = 0; ?>
            <?php foreach ($monthly_orders as $order) : ?>
                <tr>
                    <?php
                    // Mengambil tanggal dari database atau sumber lain
                    $datetimeFromDatabase = $order['create_time'];

                    $dateWithoutTime = date('d-m-Y', strtotime($datetimeFromDatabase));
                    ?>
                    <td><?php echo $order['nama_customer']; ?></td>
                    <td><?php echo $order['id_pesanan']; ?></td>
                    <td><?php echo $order['nama_produk']; ?></td>
                    <td><?php echo $order['qty_produk']; ?></td>
                    <?php $sub = $order['harga_produk'] * $order['qty_produk']; ?>
                    <td class="format"><?php echo $sub ?></td>
                    <td><?php echo $dateWithoutTime; ?></td>
                </tr>
                <?php $total += $sub; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
    <h5>Total Penjualan : <span class="format"><?php echo $total ?></span></h5>
    <div>
        <a href="<?php echo base_url('dashboard/saveaspdf'); ?>" class="btn btn-primary">Save as PDF</a>
    </div>
</div>

<script>
    function updateMonthlyReport(selectedMonth) {
        $.ajax({
            url: "<?php echo base_url('dashboard/update_monthly_report') ?>",
            type: "POST",
            data: {
                month: selectedMonth
            },
            success: function(data) {
                var result = JSON.parse(data);
                $("#monthlyReportContainer").html(result.monthlyReport);
            }
        });
    }
</script>