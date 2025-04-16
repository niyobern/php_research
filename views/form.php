<div class="form-container">
    <div class="form-title">All About ICDL Payment:</div>
    
    <?php if (!empty($message)): ?>
        <div class="message"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label>STUDENT-NAMES:</label>
            <input type="text" name="student_name" value="<?php echo isset($student['student_name']) ? htmlspecialchars($student['student_name']) : (isset($_POST['student_name']) ? htmlspecialchars($_POST['student_name']) : ''); ?>">
        </div>

        <div class="form-group">
            <label>STUDENT-NUMBER:</label>
            <input type="text" name="student_number" value="<?php echo isset($student['student_number']) ? htmlspecialchars($student['student_number']) : (isset($_POST['student_number']) ? htmlspecialchars($_POST['student_number']) : ''); ?>">
        </div>

        <div class="form-group">
            <label>DEPARTMENT:</label>
            <input type="text" name="department" value="<?php echo isset($student['department']) ? htmlspecialchars($student['department']) : (isset($_POST['department']) ? htmlspecialchars($_POST['department']) : ''); ?>">
        </div>

        <div class="form-group">
            <label>ENTER ICDL MONEY/$:</label>
            <input type="number" step="0.01" name="amount" value="<?php echo isset($_POST['amount']) ? htmlspecialchars($_POST['amount']) : ''; ?>">
        </div>

        <div class="form-group">
            <label>EXCHANGE RATE:</label>
            <input type="number" step="0.01" name="rate" value="<?php echo isset($_POST['rate']) ? htmlspecialchars($_POST['rate']) : ''; ?>">
        </div>

        <div class="exchange-group">
            <button type="submit" name="exchange">CHANGE</button>
            <?php if ($exchanged_amount !== null): ?>
                <input type="text" value="<?php echo number_format($exchanged_amount, 2); ?>" readonly>
            <?php endif; ?>
        </div>

        <div class="button-group-bottom">
            <button type="submit" name="register">Register</button>
            <button type="submit" name="display">Display</button>
            <button type="submit" name="delete">Delete</button>
        </div>
    </form>
</div> 