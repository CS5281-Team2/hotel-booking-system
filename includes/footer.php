</main>
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3 class="footer-title">About Luxury Hotel</h3>
                    <p>Experience luxury and comfort at our premium hotel with world-class service and amenities.</p>
                </div>
                
                <div class="footer-section">
                    <h3 class="footer-title">Quick Links</h3>
                    <ul>
                        <li><a href="<?php echo isset($adminPage) ? '../' : ''; ?>index.php" style="color: white;">Home</a></li>
                        <li><a href="<?php echo isset($adminPage) ? '../' : ''; ?>search.php" style="color: white;">Rooms</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3 class="footer-title">Contact</h3>
                    <p>123 Luxury Avenue<br>New York, NY 10001</p>
                    <p>Email: info@luxuryhotel.com<br>Phone: +1 234 567 8901</p>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
                <p>&copy; <?php echo date('Y'); ?> Luxury Hotel. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>