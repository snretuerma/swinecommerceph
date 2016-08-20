{{--
    This is the view to display
    the Breeder's products
    in card layouts
--}}

{{-- General Actions container --}}
<div class="row">
    <div class="col s4 left">
        {!! Form::open(['route' => 'products.updateSelected', 'id' => 'manage-selected-form']) !!}
            {{-- Add Button --}}
            <a href="#" class="btn-floating btn-large waves-effect waves-light teal darken-2 tooltipped add-product-button" data-position="top" data-delay="50" data-tooltip="Add Product">
                <i class="material-icons">add</i>
            </a>
            {{-- Select All Button --}}
            <a href="#" class="btn-floating btn-large waves-effect waves-light teal tooltipped select-all-button" data-position="top" data-delay="50" data-tooltip="Select All Products">
                <i class="material-icons">event_available</i>
            </a>
            {{-- Display selected Button. Only show when products are hidden --}}
            @if(!empty($filters['hidden']))
                <a href="#" class="btn-floating btn-large waves-effect waves-light teal lighten-2 tooltipped display-selected-button" data-position="top" data-delay="50" data-tooltip="Display all chosen">
                    <i class="material-icons">unarchive</i>
                </a>
            {{-- Hide selected Button. Only show when products are displayed --}}
        @elseif(!empty($filters['displayed']))
                <a href="#" class="btn-floating btn-large waves-effect waves-light teal lighten-2 tooltipped hide-selected-button" data-position="top" data-delay="50" data-tooltip="Hide all chosen">
                    <i class="material-icons">archive</i>
                </a>
            @endif
            {{-- Delete selected Button --}}
            <a href="#" class="btn-floating btn-large waves-effect waves-light grey tooltipped delete-selected-button" data-position="top" data-delay="50" data-tooltip="Delete all chosen">
                <i class="material-icons">delete</i>
            </a>
        {!! Form::close() !!}
    </div>

    {{-- Dropdown container --}}
    <div id="dropdown-container" class="col s8 right">
        <div class="row">
            <div id="sort-select" class="input-field col right">
                <select>
                    <option value="none">Relevance</option>
                    <option value="age-asc" @if(!empty($filters['age-asc'])) {{ $filters['age-asc'] }} @endif>Age: Low to High</option>
                    <option value="age-desc" @if(!empty($filters['age-desc'])) {{ $filters['age-desc'] }} @endif>Age: High to Low</option>
                    <option value="adg-desc" @if(!empty($filters['adg-desc'])) {{ $filters['adg-desc'] }} @endif>Average Daily Gain</option>
                    <option value="fcr-desc" @if(!empty($filters['fcr-desc'])) {{ $filters['fcr-desc'] }} @endif>Feed Conversion Ratio</option>
                    <option value="backfat_thickness-asc" @if(!empty($filters['backfat_thickness-asc'])) {{ $filters['backfat_thickness-asc'] }} @endif>Backfat Thickness</option>
                </select>
                <label>Sort By</label>
            </div>
            <div id="status-select" class="input-field col s3 right">
                <select>
                    <option value="all-status" selected>All</option>
                    <option value="displayed" @if(!empty($filters['displayed'])) {{ $filters['displayed'] }} @endif>Displayed</option>
                    <option value="hidden" @if(!empty($filters['hidden'])) {{ $filters['hidden'] }} @endif>Hidden</option>
                </select>
                <label>Status</label>
            </div>
            <div id="type-select" class="input-field col s3 right">
                <select>
                    <option value="all-type" selected>All</option>
                    <option value="boar" @if(!empty($filters['boar'])) {{ $filters['boar'] }} @endif>Boar</option>
                    <option value="sow" @if(!empty($filters['sow'])) {{ $filters['sow'] }} @endif>Sow</option>
                    <option value="semen" @if(!empty($filters['semen'])) {{ $filters['semen'] }} @endif>Semen</option>
                </select>
                <label>Show</label>
            </div>

        </div>

    </div>
</div>

{{-- Products in card elements container --}}
<div id="view-products-container" class="row">
    <?php $productNumber = 1; ?>
    @foreach($products as $product)
        <div class="col s12 m6 l4" id="product-{{$product->id}}">
            <div class="card hoverable">
                <div class="card-image">
                    @if($product->status == 'hidden')
                        <a href="{{ route('products.bViewDetail', ['product' => $product->id]) }}">
                            <img height="220" src="{{$product->img_path}}" class="hidden">
                        </a>
                    @else
                        <a href="{{ route('products.bViewDetail', ['product' => $product->id]) }}">
                            <img height="220" src="{{$product->img_path}}">
                        </a>
                    @endif
                </div>
                <div class="card-content">
                    <span class="card-title activator grey-text text-darken-4 truncate">{{$product->name}}<i class="material-icons right">more_vert</i></span>
                    <p>
                        {{$product->type}} - {{$product->breed}} <br>
                        {{$product->age}} days old <br>
                    </p>
                </div>
                <div class="card-action">
                    <div class="row">
                        <div class="col left">
                            <input type="checkbox" id="check-{{$productNumber}}" data-product-id="{{$product->id}}" class="filled-in"/>
                            <label for="check-{{$productNumber}}"></label>
                        </div>
                        <div class="col right">
                            {{-- Edit Button --}}
                            <a href="#edit-product-modal" class="modal-trigger tooltipped edit-product-button" data-position="top" data-delay="50" data-tooltip="Edit {{$product->name}}" data-product-id="{{$product->id}}">
                                <i class="material-icons teal-text text-darken-2" style="font-size:30px">edit</i>
                            </a>
                            @if(!empty($filters['hidden']) || $product->status == 'hidden')
                                {{-- Display Button --}}
                                <a href="#" class="tooltipped display-product-button" data-position="top" data-delay="50" data-tooltip="Display {{$product->name}}" data-product-id="{{$product->id}}" data-product-name="{{$product->name}}">
                                    <i class="material-icons teal-text" style="font-size:30px">unarchive</i>
                                </a>
                            @elseif(!empty($filters['displayed']) || $product->status == 'displayed')
                                {{-- Hide Button --}}
                                <a href="#" class="tooltipped hide-product-button" data-position="top" data-delay="50" data-tooltip="Hide {{$product->name}}" data-product-id="{{$product->id}}" data-product-name="{{$product->name}}">
                                    <i class="material-icons teal-text" style="font-size:30px">archive</i>
                                </a>
                            @endif
                            {{-- Delete Button --}}
                            <a href="#" class="tooltipped delete-product-button" data-position="top" data-delay="50" data-tooltip="Delete {{$product->name}}" data-product-id="{{$product->id}}">
                                <i class="material-icons grey-text text-darken-1" style="font-size:30px">delete</i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-reveal">
                    <span class="card-title grey-text text-darken-4">{{$product['name']}}<i class="material-icons right">close</i></span>
                    <p>
                        Quantity: {{$product->quantity}} <br>
                        ADG: {{$product->adg}} g<br>
                        FCR: {{$product->fcr}} <br>
                        Backfat Thickness: {{$product['backfat_thickness']}} mm <br>
                        <br>
                        <div class="col">
                            <a href="{{ route('products.bViewDetail', ['product' => $product->id]) }}" class="waves-effect waves-light btn red">View All Info</a>
                        </div>
                    </p>
                </div>
            </div>

        </div>
        <?php $productNumber++; ?>
    @endforeach
    {{-- Pagination --}}
    <div class="row">
        <div class="center-align">
            {!! $products->appends($urlFilters)->links() !!}
        </div>
    </div>
</div>
