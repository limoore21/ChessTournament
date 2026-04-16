<h2>Турнирная сетка</h2>

<?php if (empty($rounds)): ?>
    <p>Жеребьевка еще не проведена. <a href="?action=draw">Провести сейчас</a></p>
<?php else: ?>
    <div class="bracket">
        <?php foreach ($rounds as $roundNum => $matches): ?>
            <div class="round">
                <h3>Раунд <?= $roundNum ?></h3>
                <?php foreach ($matches as $match): ?>
                    <div class="match">
                        <div class="player <?= ($match['winner_id'] == $match['p1_id'] && $match['winner_id'] != null) ? 'winner' : '' ?>">
                            <?= $match['p1_name'] ?? '???' ?>
                        </div>
                        <div style="text-align: center; font-size: 10px; color: #888;">VS</div>
                        <div class="player <?= ($match['winner_id'] == $match['p2_id'] && $match['winner_id'] != null) ? 'winner' : '' ?>">
                            <?= $match['p2_name'] ?? '???' ?>
                        </div>

                        <?php if (!$match['winner_id'] && $match['p1_id'] && $match['p2_id']): ?>
                            <form method="POST" action="?action=set_winner" style="margin-top: 5px;">
                                <input type="hidden" name="match_id" value="<?= $match['id'] ?>">
                                <select name="winner_id" onchange="this.form.submit()">
                                    <option value="">Кто победил?</option>
                                    <option value="<?= $match['p1_id'] ?>"><?= $match['p1_name'] ?></option>
                                    <option value="<?= $match['p2_id'] ?>"><?= $match['p2_name'] ?></option>
                                </select>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <p><a href="?action=reset" style="color: red;" onclick="return confirm('Это удалит текущую сетку!')">Сбросить турнир</a></p>
<?php endif; ?>