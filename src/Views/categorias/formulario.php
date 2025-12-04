<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3">
                <h4 class="mb-0">
                    <i class="bi bi-<?= isset($categoria) ? 'pencil' : 'plus-lg' ?> me-2"></i>
                    <?= $titulo ?>
                </h4>
            </div>
            <div class="card-body">
                <form action="<?= BASE_URL ?>categorias/<?= isset($categoria) ? 'actualizar/' . $categoria->id : 'guardar' ?>" method="POST">
                    
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre de la Categoría <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nombre" name="nombre" 
                               value="<?= $categoria->nombre ?? '' ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= $categoria->descripcion ?? '' ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="icono" class="form-label">Icono (Bootstrap Icons)</label>
                            <select class="form-select" id="icono" name="icono">
                                <?php
                                $iconos = [
                                    'bi-tools' => 'Herramientas',
                                    'bi-cpu' => 'CPU / Hardware',
                                    'bi-window' => 'Software / Ventana',
                                    'bi-wifi' => 'Red / Wifi',
                                    'bi-printer' => 'Impresora',
                                    'bi-mouse' => 'Periféricos',
                                    'bi-display' => 'Monitor',
                                    'bi-shield-lock' => 'Seguridad',
                                    'bi-envelope' => 'Correo',
                                    'bi-telephone' => 'Teléfono',
                                    'bi-question-circle' => 'Otro'
                                ];
                                foreach ($iconos as $icon => $label): ?>
                                    <option value="<?= $icon ?>" <?= ($categoria->icono ?? '') == $icon ? 'selected' : '' ?>>
                                        <?= $label ?> (<?= $icon ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="color" class="form-label">Color Identificativo</label>
                            <input type="color" class="form-control form-control-color w-100" id="color" name="color" 
                                   value="<?= $categoria->color ?? '#6c757d' ?>" title="Elige un color">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="<?= BASE_URL ?>categorias" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-2"></i>Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
