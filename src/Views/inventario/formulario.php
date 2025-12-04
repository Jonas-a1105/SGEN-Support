<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= $titulo ?></h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Datos del Artículo</h6>
        </div>
        <div class="card-body">
            <form action="<?= BASE_URL ?>inventario/crear" method="POST">
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="codigo" class="form-label">Código *</label>
                        <input type="text" name="codigo" id="codigo" class="form-control" required>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label for="nombre" class="form-label">Nombre del Artículo *</label>
                        <input type="text" name="nombre" id="nombre" class="form-control" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="categoria" class="form-label">Categoría *</label>
                        <select name="categoria" id="categoria" class="form-select" required>
                            <option value="">Seleccionar...</option>
                            <option value="Hardware">Hardware</option>
                            <option value="Software">Software</option>
                            <option value="Periféricos">Periféricos</option>
                            <option value="Cables">Cables</option>
                            <option value="Consumibles">Consumibles</option>
                            <option value="Herramientas">Herramientas</option>
                            <option value="Otros">Otros</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="marca" class="form-label">Marca</label>
                        <input type="text" name="marca" id="marca" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="modelo" class="form-label">Modelo</label>
                        <input type="text" name="modelo" id="modelo" class="form-control">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="descripcion" class="form-label">Descripción</label>
                    <textarea name="descripcion" id="descripcion" class="form-control" rows="3"></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="unidad_medida" class="form-label">Unidad de Medida</label>
                        <select name="unidad_medida" id="unidad_medida" class="form-select">
                            <option value="Unidad">Unidad</option>
                            <option value="Caja">Caja</option>
                            <option value="Metro">Metro</option>
                            <option value="Paquete">Paquete</option>
                            <option value="Litro">Litro</option>
                            <option value="Kg">Kg</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="stock_inicial" class="form-label">Stock Inicial</label>
                        <input type="number" name="stock_inicial" id="stock_inicial" class="form-control" value="0" min="0">
                        <small class="text-muted">Se asignará al Almacén Central</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="stock_minimo" class="form-label">Stock Mínimo (Alerta)</label>
                        <input type="number" name="stock_minimo" id="stock_minimo" class="form-control" value="5" min="0">
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="font-weight-bold text-primary mb-3">Datos de Compra y Garantía</h6>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="fecha_compra" class="form-label">Fecha de Compra</label>
                        <input type="date" name="fecha_compra" id="fecha_compra" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="proveedor" class="form-label">Proveedor</label>
                        <input type="text" name="proveedor" id="proveedor" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="proveedor_rif" class="form-label">RIF del Proveedor</label>
                        <input type="text" name="proveedor_rif" id="proveedor_rif" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="garantia_fin" class="form-label">Vencimiento de la Garantía</label>
                        <input type="date" name="garantia_fin" id="garantia_fin" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="valor_compra" class="form-label">Valor de Compra</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="valor_compra" id="valor_compra" class="form-control" step="0.01" min="0">
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <a href="<?= BASE_URL ?>inventario" class="btn btn-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-success">Guardar Producto</button>
                </div>
            </form>
        </div>
    </div>
</div>