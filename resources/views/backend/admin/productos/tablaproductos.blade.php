<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <table id="table" class="table table-bordered">
                        <thead>
                        <tr>
                            <th style="width: 90px; white-space: nowrap" class="text-center">Posición</th>
                            <th>Nombre</th>
                            <th style="width: 130px; white-space: nowrap" class="text-center">Activo</th>
                            <th style="width: 100px; white-space: nowrap" class="text-right">Precio</th>
                            <th style="width: 120px; white-space: nowrap" class="text-center">Imagen</th>
                            <th style="width: 230px; white-space: nowrap">Opciones</th>
                        </tr>
                        </thead>
                        <tbody id="tablecontents">
                        @foreach($productos as $dato)
                            <tr class="row1" data-id="{{ $dato->id }}">

                                <td class="text-center">{{ $dato->posicion }}</td>
                                <td>{{ $dato->nombre }}</td>
                                <td class="text-center">
                                    @if($dato->activo == 0)
                                        <span class="badge bg-danger">Desactivado</span>
                                    @else
                                        <span class="badge bg-success">Activado</span>
                                    @endif
                                </td>

                                <td class="text-right">{{ $dato->precio }}</td>

                                <td class="text-center">
                                    @if($dato->utiliza_imagen == 1)
                                    <img alt="Imagenes" src="{{ url('storage/imagenes/'.$dato->imagen) }}" style="width: 90px; height: 90px; object-fit: cover; border-radius: 6px" />
                                    @endif
                                </td>

                                <td style="white-space: nowrap">
                                    <button type="button" class="btn btn-primary btn-xs" onclick="informacion({{ $dato->id }})">
                                        <i class="fas fa-eye" title="Editar"></i>&nbsp; Editar
                                    </button>

                                    <a class="btn btn-info btn-xs" style="margin-top: 4px" href="{{ url('/admin/productos/modificadores/'.$dato->id) }}">
                                        <i class="fas fa-list"></i>&nbsp; Modificadores
                                        @if(($dato->total_modificadores ?? 0) > 0)
                                            <span class="badge badge-light">{{ $dato->total_modificadores }}</span>
                                        @endif
                                    </a>

                                </td>

                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<script type="text/javascript">
    $(document).ready(function() {

        $( "#tablecontents" ).sortable({
            items: "tr",
            cursor: 'move',
            opacity: 0.6,
            update: function() {
                sendOrderToServer();
            }
        });

        function sendOrderToServer() {

            var order = [];
            $('tr.row1').each(function(index,element) {
                order.push({
                    id: $(this).attr('data-id'),
                    posicion: index+1
                });
            });

            openLoading();

            axios.post('/admin/productos/ordenar',  {
                'order': order
            })
                .then((response) => {
                    closeLoading();
                    toastr.success('Actualizado correctamente');
                    recargar();
                })
                .catch((error) => {
                    closeLoading();
                    toastr.error('Error al actualizar');
                });
        }
    });

</script>
