<h2>Турнирная сетка</h2>

<?php if (empty($rounds)): ?>
    <div style="padding: 20px; border: 1px dashed #ccc; background: #f9f9f9;">
        <p>Данные в базе не найдены или жеребьевка еще не проводилась.</p>
        <a href="index.php?action=draw" style="font-weight: bold; color: blue; text-decoration: underline;">
            Нажмите сюда, чтобы запустить жеребьевку
        </a>
    </div>
<?php else: ?>
    <div style="display: flex; gap: 30px; align-items: flex-start;">
        <?php foreach ($rounds as $round_id => $matches): ?>
            <div class="round-column">
                <h4 style="background: #eee; padding: 5px; text-align: center;">Раунд <?= $round_id ?></h4>
                <?php foreach ($matches as $match): ?>
                    <div style="border: 1px solid #000; margin-bottom: 10px; padding: 10px; width: 180px; background: #fff;">
                        <div style="border-bottom: 1px solid #ccc; padding-bottom: 5px;">
                            <strong>1:</strong> <?= htmlspecialchars($match['p1_name'] ?? '---') ?>
                        </div>
                        <div style="padding-top: 5px;">
                            <strong>2:</strong> <?= htmlspecialchars($match['p2_name'] ?? '---') ?>
                        </div>
                        <?php if ($match['winner_id']): ?>
                            <div style="margin-top: 5px; color: green; font-size: 0.8em;">
                                Победитель: <?= htmlspecialchars($match['winner_name'] ?? 'ID ' . $match['winner_id']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div style="margin-top: 40px;">
        <a href="index.php?action=draw" onclick="return confirm('Это полностью сбросит текущую сетку. Продолжить?')"
           style="color: red; font-size: 0.9em;">Сбросить и пересоздать сетку</a>
    </div>
<?php endif; ?>