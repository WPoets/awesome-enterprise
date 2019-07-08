/*
    used in shortcodemixed-hint.js
    this array is used to autocomplete shortcodes
*/
var s = { attrs: {} }; // Simple tag, reused for a whole lot of tags
var shortcodes = {
  "aw2.module": {
    attrs:{
      slug: [""],
      module: [""],
      template: [""],
      post_type: [""]
    },
    params: "slug, module, template, post_type",
    doc: "Call a Module",
    return: "String or Object"
  },
  "aw2.this": {
    attrs:{},
    doc: "Set Module Parameter"
  },
  "aw2.echo": {
    attrs:{},
    doc: "Echo a Chain",
    return: "array"
  },
  "aw2.set": {
    attrs:{},
    doc: "Set a chain"
  },
  "aw2.set_array": {
    attrs:{},
    doc: "Set an array"
  },
  "aw2.get": {
    attrs:{
      default: [""]
    },
    params: "default",
    doc: "Get a variable",
    return: "Return a variable"
  },
  "aw2.raw": {
    attrs:{
      default: [""]
    },
    params: "default",
    doc: "Get a Raw Value. Will not be parsed"
  },
  "aw2.die": {
    attrs:{},
    doc: "Echo a chain and die",
  },
  "aw2.switch": {
    attrs:{},
    doc: "Initiate a switch case"
  },
  "aw2.case": {
    attrs:{},
    doc: "Conditional check of the case"
  },
  "aw2.case_else": {
    attrs:{},
    doc: "Default case"
  },
  "aw2.save_form": {
    attrs:{
      tag: [""],
      set_post_id: [""]
    },
    params: "tag, set_post_id",
    doc: "Save Form",
    return: "Post ID"
  },
  "aw2.destroy_sessions": {
    attrs:{},
    doc: "Destroy Sessions"
  },
  "code.run": {
    attrs:{},
    doc: "Run the Code Library"
  },
  "content.get": {
    attrs:{},
    doc: "Get the raw content"
  },
  "content.run": {
    attrs:{},
    doc: "Run the content"
  },
  "query.get_post": {
    attrs:{
      post_id: [""],
      post_slug: [""],
      post_type: [""],
    },
    params: "post_id, post_slug, post_type",
    doc: "Get post by post_slug and post_type. or by passing the post_id",
    return: "Wordpress Post"
  },
  "query.get_post_terms": {
    attrs:{
      post_id: [""],
      taxonomy: [""],
      orderby: [""],
      order: [""],
      fields: [""]
    },
    params: "post_id, taxonomy, orderby, order, fields",
    doc: "Retrieve the terms for a post",
    return: "An array of taxonomy terms, or empty array if no terms are found"
  },
  "query.get_post_meta": {
    attrs:{
      post_id: [""],
      key: [""],
      single: [""]
    },
    params: "post_id, key, single",
    doc: "Calls get_post_meta. key is used to retrieve specific key meta. By default, returns data for all keys",
    return: "(mixed) Will be an array if single is false. Will be value of meta data field if single is true."
  },
  "query.all_post_meta": {
    attrs:{
      post_id: [""],
      post_slug: [""],
      post_type: [""],
    },
    params: "post_id, post_slug, post_type",
    doc: "Get post meta by post_slug and post_type. or by passing the post_id",
    return: "WP post meta"
  },
  "query.insert_post": {
    attrs:{
      args: [""]
    },
    params: "args",
    doc: "Insert or update a post. if args  has 'ID' set to a value, then post will be updated. args can be array or json",
    return: "The post ID on success. The value 0 or WP_Error on failure."
  },
  "query.update_post": {
    attrs:{
      args: [""]
    },
    params: "args",
    doc: "Update a post with new post data. args can be array or json",
    return: "The value 0 or WP_Error on failure. The post ID on success"
  },
  "query.update_post_status": {
    attrs:{
      post_id: [""],
      post_status: [""]
    },
    params: "post_id, post_status",
    doc: "Update post status by calling wp_update_post function"
  },
  "query.update_post_meta": {
    attrs:{
      post_id: [""],
      meta_key: [""],
      meta_value: [""],
      prev_value: [""]
    },
    params: "post_id, meta_key, meta_value, prev_value",
    doc: "If meta_key, meta_value is provided then single meta_key is updated or post meta will be updated from json args"
  },
  "query.delete_post_meta": {
    attrs:{
      post_id: [""],
      meta_key: [""],
      meta_value: [""]
    },
    params: "post_id, meta_key, meta_value",
    doc: "Delete post meta by calling delete_post_meta"
  },
  "query.add_non_unique_post_meta": {
    attrs:{
      post_id: [""],
      meta_key: [""],
      meta_value: [""]
    },
    params: "post_id, meta_key, meta_value",
    doc: "Adds a custom field (also called meta-data) to a specified post which could be of any post type. A custom field is effectively a key–value pair"
  },
  "query.delete_post": {
    attrs:{
      post_id: [""],
      force_delete: [""]
    },
    params: "post_id, force_delete",
    doc: "Delete's post by calling wp_delete_post"
  },
  "query.trash_post": {
    attrs:{
      post_id: [""]
    },
    params: "post_id",
    doc: "Moves a post or page to the Trash. If trash is disabled, the post or page is permanently deleted."
  },
  "query.set_post_terms": {
    attrs:{
      post_id: [""],
      terms: [""],
      slugs: [""],
      taxonomy: [""],
      append: [""]
    },
    params: "post_id, terms, slugs, taxonomy, append",
    doc: "Relates an object (post, link etc) to a term and taxonomy type (tag, category, etc). Creates the term and taxonomy relationship if it doesn't already exist"
  },
  "query.get_posts": {
    attrs:{
      name:[""],
      posts_per_page: [""],
      offset: [""],
      category: [""],
      category_name: [""],
      orderby: [""],
      order:[""],
      include:[""],
      exclude: [""],
      meta_key: [""],
      meta_value: [""],
      post_type: [""],
      post_mime_type: [""],
      post_parent: [""],
      author: [""],
      post_status: [""],
      suppress_filters: [""]
    },
    params: "name, posts_per_page, offset, category, category_name, orderby, order, include, exclude, meta_key, meta_value, post_type, post_mime_type, post_parent, author, post_status, suppress_filters",
    doc: "It retrieves a list of recent posts or posts matching this criteria",
    return: "WP posts array"
  },
  "query.get_pages": {
    attrs:{
      sort_order: [""],
      sort_column: [""],
      hierarchical: [""],
      exclude: [""],
      include: [""],
      meta_key: [""],
      meta_value: [""],
      authors: [""],
      child_of: [""],
      parent: [""],
      exclude_tree: [""],
      number: [""],
      offset: [""],
      post_type: [""],
      post_status: [""]
    },
    params: "sort_order, sort_column, hierarchical, exclude, include, meta_key, meta_value, authors, child_of, parent, exclude_tree, number, offset, post_type, post_status",
    doc: "This function returns an array of pages that are in the blog, optionally constrained by parameters",
    return: "An array containing all the Pages matching the request, or false on failure."
  },
  "query.wp_query": {
    attrs:{
      args: [""]
    },
    params: "args",
    return: "new WP_Query"
  },
  "query.get_term_by": {
    attrs:{
      field: [""],
      value: [""],
      taxonomy: [""],
      output: [""],
      filter: [""]
    },
    params: "field, value, taxonomy, output, filter",
    doc: "Get all Term data from database by Term field and data",
    return: "Term Row (object or array) from database. Will return false if taxonomy does not exist or term was not found. Othewise returns object (by default) or array of fields depending on output parameter"
  },
  "query.get_term_meta": {
    attrs:{
      single: [""],
      term_id: [""],
      key: [""]
    },
    params: "single, term_id, key",
    doc: "Retrieves metadata for a term",
    return: "(mixed) If single is false, an array of metadata values. If single is true, a single metadata value"
  },
  "query.insert_term": {
    attrs:{
      term: [""],
      taxonomy: [""],
      alias_of: [""],
      description: [""],
      parent: [""],
      slug: [""]
    },
    params: "term, taxonomy, alias_of, description, parent, slug",
    doc: "Adds a new term to the database. Optionally marks it as an alias of an existing term"
  },
  "delete_term": {
    attrs:{
      term_id: [""],
      taxonomy: [""]
    },
    params: "term_id, taxonomy",
    doc: "Removes a term from the database"
  },
  "query.get_terms": {
    attrs:{
      taxonomies: [""],
      orderby: [""],
      order: [""],
      hide_empty: [""],
      include: [""],
      exclude: [""],
      exclude_tree: [""],
      number: [""],
      offset: [""],
      fields: [""],
      name: [""],
      slug: [""],
      hierarchical: [""],
      search: [""],
      name__like: [""],
      description__like: [""],
      pad_counts: [""],
      get: [""],
      child_of: [""],
      parent: [""],
      childless: [""],
      cache_domain: [""],
      update_term_meta_cache: [""],
      meta_query: [""]
    },
    params: "taxonomies, orderby, order, hide_empty, include, exclude, exclude_tree, number, offset, fields, name, slug, hierarchical, search, name__like, description__like, pad_counts, get, child_of, parent, childless, cache_domain, update_term_meta_cache, meta_query",
    doc: "Retrieve the terms in a given taxonomy or list of taxonomies",
    return: "List of WP_Term instances and their children. Will return WP_Error, if any of taxonomies do not exist"
  },
  "query.get_comment": {
    attrs:{
      id: [""],
      output: [""]
    },
    params: "id, output",
    doc: "Retrieves comment data given a comment ID or comment object",
    return: "(WP_Comment|array|null) Depends on output value"
  },
  "query.get_comments": {
    attrs:{
      args: [""]
    },
    params: "args",
    doc: "Retrieve a list of comments. by providing JSON data",
    return: "List of comments or number of found comments if count argument is true"
  },
  "query.get_results": {
    attrs:{},
    doc: "Generic, multiple row results can be pulled from the database with get_results",
    return: "The entire query result as an array"
  },
  "query.get_row": {
    attrs:{},
    doc: "Retrieve one row from the database",
    return: "Database query result in format ARRAY_A or null on failure"
  },
  "query.get_col": {
    attrs:{},
    doc: "Retrieve one column from the database",
    return: "Database query result. Array indexed from 0 by SQL result row number."
  },
  "query.get_var": {
    attrs:{},
    doc: "Retrieve one variable from the database",
    return: "Database query result (as string), or null on failure"
  },
  "query.query": {
    attrs:{},
    doc: "Perform a MySQL database query, using current database connection"
  },
  "query.get_user_by": {
    attrs:{
      field: [""],
      value: [""]
    },
    params: "field, value",
    doc: "Retrieve user info by a given field",
    return: "WP_User object on success, false on failure"
  },
  "query.update_user_meta": {
    attrs:{
      user_id: [""],
      meta_key: [""],
      meta_value: [""],
      prev_value: [""]
    },
    params: "user_id, meta_key, meta_value, prev_value",
    doc: "Update user meta field based on user ID. Use the prev_value parameter to differentiate between meta fields with the same key and user ID. If the meta field for the user does not exist, it will be added."
  },
  "query.get_user_meta": {
    attrs:{
      user_id: [""],
      key: [""],
      single: [""]
    },
    params: "user_id, key, single",
    doc: "Retrieve a single meta field or all fields of user_meta data for the given user. Uses get_metadata()",
    return: "Will be an Array if key is not specified or if single is false. Will be value of meta_value field if single is true."
  },
  "query.get_users": {
    attrs:{
      blog_id: [""],
      role: [""],
      meta_key: [""],
      meta_value: [""],
      meta_compare: [""],
      meta_query: [""],
      date_query: [""],
      include: [""],
      exclude: [""],
      orderby: [""],
      order: [""],
      offset: [""],
      search: [""],
      number: [""],
      count_total: [""],
      fields: [""],
      who: [""]
    },
    params: "blog_id, role, meta_key, meta_value, meta_compare, meta_query, date_query, include, exclude, orderby, order, offset, search, number, count_total, fields, who",
    doc: "Retrieves an array of users matching the criteria given in args",
    return: "An array of IDs, stdClass objects, or WP_User objects, depending on the value of the 'fields' parameter"
  },
  "query.users_builder": {
    attrs:{
      part: ["start","meta_query","date_query","run"]
    },
    params: "part = start, meta_query, date_query, run",
    doc: "WP_User_Query is a class",
    return: "An array of IDs, stdClass objects, or WP_User objects, depending on the value of the 'fields' parameter"
  },
  "query.posts_builder": {
    attrs:{
      part: ["start","tax_query","meta_query","date_query","run"]
    },
    params: "part = start, tax_query, meta_query, date_query, run",
    return: "new WP_Query"
  },
  "query.insert_comment": {
    attrs:{
      comment_post_ID: [""],
      post_id: [""],
      author_name: [""],
      author_email: [""],
      author_url: [""],
      type: [""],
      parent: [""],
      user_id: [""],
      approved: [""],
    },
    params: "comment_post_ID, post_id, author_name, author_email, author_url, type, parent, user_id, approved",
    doc: "Inserts a comment to the database",
    return: "The new comment's ID"
  },
  "query.delete_revisions": {
    attrs:{
      post_id: [""]
    },
    params: "post_id",
    doc: "Deletes all the revisions of the post",
    return: "Return no. of affected rows."
  },
  "query.term_exists": {
    attrs:{
      term: [""],
      taxonomy: [""]
    },
    params: "term, taxonomy",
    doc: "Check if a given term exists and return the term ID, a term array, or 0 (false) if the term doesn't exist",
    return: "Returns 0 or NULL if the term does not exist. Returns the term ID if no taxonomy was specified and the term exists. Returns an array if the parent exists"
  },
  "int.get": {
    attrs:{
      default: [""]
    },
    params: "default",
    doc: "Returns value as an Integer",
    return: "Integer value"
  },
  "int.create": {
    attrs:{},
    doc: "Create & Return value as an Integer",
    return: "Integer value"
  },
  "str.get": {
    attrs:{
      default: [""]
    },
    params: "default",
    doc: "Returns value as a String",
    return: "String value"
  },
  "str.create": {
    attrs:{},
    doc: "Create & return value as a String",
    return: "String value"
  },
  "num.get": {
    attrs:{
      default: [""]
    },
    params: "default",
    doc: "Returns value as a Float",
    return: "float"
  },
  "num.create": {
    attrs:{
    },
    doc: "Create & return value as a Float",
    return: "float"
  },
  "bool.get": {
    attrs:{
      default: [""]
    },
    params: "default",
    doc: "Returns value as a Boolean",
    return: "boolean"
  },
  "bool.create": {
    attrs:{},
    doc: "Create & return value as a Boolean",
    return: "boolean"
  },
  "date.get": {
    attrs:{
      default: [""]
    },
    params: "default",
    doc: "Returns DateTime",
    return: "new DateTime"
  },
  "date.create": {
    attrs:{
      default: [""]
    },
    params: "default",
    doc: "Create & return DateTime",
    return: "new DateTime"
  },
  "date.diff": {
    attrs:{
      type: ["mins","hours","days","english"],
      date_from: [""],
      date_to: [""]
    },
    params: "type(default:mins can be set to mins,hours,days,english), date_from, date_to",
    doc: "returns the differnce between two dates",
    return: "date difference"
  },
  "date.aw2_period": {
    attrs:{
      period: [""]
    },
    params: "period",
    doc: "Given a period, returns start_date and end_date",
    return: "array of start_time and end_time"
  },
  "arr.set": {
    attrs:{},
    doc: "Set a value in an array"
  },
  "arr.create": {
    attrs:{},
    doc: "Build an array",
    return: "array"
  },
  "debug.ignore": {
    attrs:{},
    doc: "Ignore what is inside"
  },
  "elastic_email.send": {
    attrs:{
      array: [""]
    },
    params: "array",
    doc: "Send a Mail using Elastic Mail. if log_messages is on in site_settings then msg log also maintain.",
    return: "Returns api response"
  },
  "env.get": {
    attrs:{
      _prefix: [""],
      default: [""]
    },
    params: "_prefix, default",
    doc: "Get an Environment Value",
    return: "Return Environment variable value"
  },
  "env.set": {
    attrs:{},
    doc: "Set an Environment Value"
  },
  "env.set_raw": {
    attrs:{
      _prefix: [""]
    },
    params: "_prefix",
    doc: "Set a Raw Value. Will not be parsed"
  },
  "env.set_array": {
    attrs:{},
    doc: "Set an Array"
  },
  "env.dump": {
    attrs:{
      _prefix: [""]
    },
    params: "_prefix",
    doc: "Dump an environment Value",
    return: "return var_dump"
  },
  "env.echo": {
    attrs:{
      _prefix: [""]
    },
    params: "_prefix",
    doc: "Dump an environment Value",
    return: "return var_dump"
  },
  "excel.write_bulk": {
    attrs:{
      file_name: [""],
      folder: [""],
      file_format: [""],
      data: [""],
      template_file: [""],
      template_folder: [""]
    },
    params: "file_name, folder, file_format(default: Excel2007), data, template_file, template_folder",
    doc: "Bulk write in the excel file. Use excel.write_bulk"
  },
  "excel.file_reader": {
    attrs:{
      file_path: [""],
      folder: [""],
      file_format: [""],
      start_from: [""],
      limit: [""]
    },
    params: "file_path, folder, file_format(default: Excel2007), start_from(default: 2), limit",
    doc: "Read the excel file",
    retrun: "array"
  },
  "excel.info": {
    attrs:{
      file_path: [""],
      file_format: [""]
    },
    params: "file_path, file_format(default: Excel2007)",
    doc: "Read and return the excel info",
    return: "total_rows"
  },
  "excel.dataset_write": {
    attrs:{
      file_name: [""],
      folder: [""],
      file_format: [""],
      dataset: [""],
      template_file: [""],
      template_folder: [""]
    },
    params: "file_name, folder, file_format(default: Excel2007), dataset, template_file, template_folder",
    doc: "Write the excel file"
  },
  "excel.write_bulk_csv": {
    attrs:{
      file_name: [""],
      folder: [""],
      data: [""]
    },
    params: "file_name, folder, data",
    doc: "Bulk write the excel file as csv"
  },
  "excel.read_header": {
    attrs:{
      file_name: [""],
      folder: [""]
    },
    params: "file_name, folder",
    doc: "Read the header info of the excel file",
    return: "array"
  },
  "excel.read_post_data": {
    attrs:{
      file_name: [""],
      folder: [""],
      posts_per_page: [""],
      offset: [""]
    },
    params: "file_name, folder, posts_per_page, offset(default: 0)",
    doc: "Read the post data",
    return: "array"
  },
  "excel.read_bulk": {
    attrs:{
      file_name: [""],
      folder: [""],
      file_format: [""],
      data: [""],
      template_file: [""],
      template_folder: [""]
    },
    params: "file_name, folder, file_format(default: Excel2007), data, template_file, template_folder",
    doc: "Bulk read the excel file",
    return: "var_dump"
  },
  "facebook.login_url": {
    attrs:{
      ticket_id: [""],
      scope: [""],
      app_id: [""],
      app_secret: [""]
    },
    params: "ticket_id, scope, app_id, app_secret",
    doc: "returns the login URL for facebook"
  },
  "facebook.auth": {
    attrs:{
      ticket_id: [""],
      scope: [""],
      app_id: [""],
      app_secret: [""]
    },
    params: "ticket_id, scope, app_id, app_secret",
    doc: "Check the auth for facebook"
  },
  "file.write": {
    attrs:{
      file_name: [""],
      folder: [""],
      child_folder: [""],
      mode: [""],
      content_to_write: [""]
    },
    params: "file_name, folder, child_folder, mode, content_to_write",
    doc: "Write content to file",
    return: "File path or error"
  },
  "aw2.upload": {
    attrs:{
      main: ["attach_to_post","upload_to_path"],
      post_id: [""],
      upload_element_id: [""],
      upload_file_url: [""],
      dir_name: [""],
      file_name: [""],
      overwrite_file: [""],
      allowed_file_types: [""],
      set_featured: [""],
      woo_product_gal: [""]
    },
    params: "main(default: attach_to_post), post_id, upload_element_id, upload_file_url, dir_name, file_name, overwrite_file, allowed_file_types, set_featured, woo_product_gal",
    doc: "Upload"
  },
  "aw2.sideload": {
    attrs:{
      main: ["attach_to_post","save_to_path"],
      post_id: [""],
      file_url: [""],
      dir_path: [""],
      file_name: [""],
      overwrite_file: [""],
      resize: [""],
      sizes: [""],
      crop: [""],
      attach: [""],
      allowed_file_types: [""],
      set_featured: [""],
      woo_product_gal: [""]
    },
    params: "main (defaut: attach_to_post cand be set to save_to_path), post_id, file_url, dir_path, file_name, overwrite_file, resize, sizes, crop, attach, allowed_file_types, set_featured, woo_product_gal",
    doc: "Download a File from URL and attach to media"
  },
  "global_cache.set": {
    attrs:{
      key: [""],
      prefix: [""],
      ttl: [""]
    },
    params: "key ,prefix ,ttl(default: 300)",
    doc: "Set the Global Cache"
  },
  "global_cache.get": {
    attrs:{
      prefix: [""]
    },
    params: "prefix",
    doc: "Get the Global Cache",
    return: "value from the redis database"
  },
  "global_cache.exists": {
    attrs:{
      prefix: [""]
    },
    params: "prefix",
    doc: "if exists in the global cache"
  },
  "global_cache.flush": {
    attrs:{},
    doc: "Flush the Global Cache"
  },
  "hashids.set": {
    attrs:{
      value: [""],
      prefix: [""]
    },
    params: "value, prefix(default: aw2_token_)",
    doc: "Set the hashids in the Options table"
  },
  "hashids.get": {
    attrs:{
      hash: [""],
      prefix: [""]
    },
    params: "hash, prefix(default: aw2_token_)",
    doc: "Get the hashids in the Options table"
  },
  "if.equal": {
    attrs:{
      lhs: [""],
      rhs: [""]
    },
    params: "lhs, rhs",
    doc: "if lhs=rhs then executes"
  },
  "if.not_equal": {
    attrs:{
      lhs: [""],
      rhs: [""]
    },
    params: "lhs, rhs",
    doc: "if lhs != rhs then executes"
  },
  "if.greater_equal": {
    attrs:{
      lhs: [""],
      rhs: [""]
    },
    params: "lhs, rhs",
    doc: "if lhs>=rhs then executes"
  },
  "if.greater_than": {
    attrs:{
      lhs: [""],
      rhs: [""]
    },
    params: "lhs, rhs",
    doc: "if lhs>rhs then executes"
  },
  "if.less_equal": {
    attrs:{
      lhs: [""],
      rhs: [""]
    },
    params: "lhs, rhs",
    doc: "if lhs<=rhs then executes"
  },
  "if.less_than": {
    attrs:{
      lhs: [""],
      rhs: [""]
    },
    params: "lhs, rhs",
    doc: "if lhs<rhs then executes"
  },
  "if.else": {
    attrs: {},
    doc: "else condition"
  },
  "if.and": {
    attrs:{
      lhs: [""],
      rhs: [""]
    },
    params: "lhs, rhs",
    doc: "and condition"
  },
  "if.or": {
    attrs:{
      lhs: [""],
      rhs: [""]
    },
    params: "lhs, rhs",
    doc: "or condition"
  },
  "if.contains": {
    attrs:{
      needle: [""],
      haystack: [""]
    },
    params: "needle(key), haystack(comma separated string)",
    doc: "if haystack contains needle then executes"
  },
  "if.not_contains": {
    attrs:{
      needle: [""],
      haystack: [""]
    },
    params: "needle(key), haystack(comma separated string)",
    doc: "if haystack not contains needle then executes"
  },
  "if.whitespace": {
    attrs:{},
    doc: "If provided variable is whitespace then executes"
  },
  "if.not_whitespace": {
    attrs:{},
    doc: "If provided variable is not_whitespace then executes"
  },
  "if.false": {
    attrs:{},
    doc: "If provided variable is false then executes"
  },
  "if.true": {
    attrs:{},
    doc: "If provided variable is true then executes"
  },
  "if.yes": {
    attrs:{},
    doc: "If provided variable is yes then executes"
  },
  "if.no": {
    attrs:{},
    doc: "If provided variable is no then executes"
  },
  "if.not_empty": {
    attrs:{},
    doc: "If provided variable is not_empty then executes"
  },
  "if.empty": {
    attrs:{},
    doc: "If provided variable is empty then executes"
  },
  "if.odd": {
    attrs:{},
    doc: "If provided variable is odd then executes"
  },
  "if.even": {
    attrs:{},
    doc: "If provided variable is even then executes"
  },
  "if.arr": {
    attrs:{},
    doc: "If provided variable is array then executes"
  },
  "if.not_arr": {
    attrs:{},
    doc: "If provided variable is not array then executes"
  },
  "if.str": {
    attrs:{},
    doc: "If provided variable is string then executes"
  },
  "if.not_str": {
    attrs:{},
    doc: "If provided variable is not string then executes"
  },
  "if.bool": {
    attrs:{},
    doc: "If provided variable is bool then executes"
  },
  "if.not_bool": {
    attrs:{},
    doc: "If provided variable is not bool then executes"
  },
  "if.greater_than_zero": {
    attrs:{},
    doc: "If provided variable is greater than zero then executes"
  },
  "if.num": {
    attrs:{},
    doc: "If provided variable is number then executes"
  },
  "if.not_num": {
    attrs:{},
    doc: "If provided variable is not number then executes"
  },
  "if.int": {
    attrs:{},
    doc: "If provided variable is int then executes"
  },
  "if.not_int": {
    attrs:{},
    doc: "If provided variable is not int then executes"
  },
  "if.date_obj": {
    attrs:{},
    doc: "If provided variable is date object then executes"
  },
  "if.not_date_obj": {
    attrs:{},
    doc: "If provided variable is not date obj then executes"
  },
  "if.obj": {
    attrs:{},
    doc: "If provided variable is obj then executes"
  },
  "if.not_obj": {
    attrs:{},
    doc: "If provided variable is not_obj then executes"
  },
  "if.user_can": {
    attrs:{},
    doc: "Whether a user has capability or role"
  },
  "if.user_cannot": {
    attrs:{},
    doc: "Whether a user dont have capability or role"
  },
  "if.logged_in": {
    attrs:{},
    doc: "If current user is wp logged in then executes"
  },
  "if.not_logged_in": {
    attrs:{},
    doc: "If current user is not wp logged in then executes"
  },
  "if.request": {
    attrs:{},
    doc: "If request exists"
  },
  "if.not_request": {
    attrs:{},
    doc: "If not request exists"
  },
  "if.device": {
    attrs:{},
    doc: "checks device"
  },
  "linkedin.login_url": {
    attrs:{
      ticket_id: [""],
      scope: [""],
      app_id: [""],
      app_secret: [""]
    },
    params: "ticket_id, scope, app_id, app_secret",
    doc: "returns the login URL for linkedin"
  },
  "linkedin.auth": {
    attrs:{
      ticket_id: [""],
      scope: [""],
      app_id: [""],
      app_secret: [""]
    },
    params: "ticket_id, scope, app_id, app_secret",
    doc: "Check the auth for linkedin"
  },
  "math.solve": {
    attrs:{},
    doc: "Run the Code Library",
    return: "solved value on success"
  },
  "module.run": {
    attrs:{},
    doc: "Run an arbitrary module"
  },
  "module.include": {
    attrs:{},
    doc: "Include an arbitrary module"
  },
  "module.return": {
    attrs:{},
    doc: "Return an active module"
  },
  "multi.update": {
    attrs:{},
    doc: "Update Multi Query"
  },
  "multi.select": {
    attrs:{},
    doc: "Select Multi Query"
  },
  "notify.wpmail": {
    attrs:{
      email: [""],
      log: [""],
      notification_object_type: [""],
      notification_object_id: [""],
    },
    params: "email, log, notification_object_type, notification_object_id",
    doc: "Send wp mail",
    return: "return success string on success"
  },
  "notify.sendgrid": {
    attrs:{
      email: [""],
      log: [""],
      notification_object_type: [""],
      notification_object_id: [""],
    },
    params: "email, log, notification_object_type, notification_object_id",
    doc: "Send Sendgrid mail",
    return: "return success string on success"
  },
  "notify.kookoo": {
    attrs:{
      sms: [""],
      log: [""],
      notification_object_type: [""],
      notification_object_id: [""],
    },
    params: "sms, log, notification_object_type, notification_object_id",
    doc: "Send Kookoo SMS",
    return: "return api status"
  },
  "pdf.generate": {
    attrs:{},
    doc: "PDF Generate"
  },
  "phantomjs.generate": {
    attrs:{
      url: [""],
      output_folder: [""],
      output_file: [""],
      format: [""],
      orientation: [""],
      margin: [""]
    },
    params: "url, output_folder, output_file, format(default: A4), orientation(default: landscape), margin(default: 1cm)",
    doc: "Generate a PDF from URL or HTML"
  },
  "query.meta_query": {
    attrs:{},
    doc: "Run Custom Meta Query"
  },
  "request.get": {
    attrs:{
      default: [""]
    },
    params: "default",
    doc: "Get the request from URL"
  },
  "request.dump": {
    attrs:{},
    doc: "Get the request from URL and dump"
  },
  "request.echo": {
    attrs:{},
    doc: "Echo the request from URL"
  },
  "service.run": {
    attrs:{
      service: [""],
      template: [""],
      module: [""]
    },
    params: "service, template, module",
    doc: "Used to run a service"
  },
  "services.add": {
    attrs:{
      desc: [""]
    },
    params: "desc",
    doc: "Add a New Service"
  },
  "services.remove": {
    attrs:{},
    doc: "Remove a Service"
  },
  "session_cache.set": {
    attrs:{
      key: [""],
      value: [""],
      prefix: [""],
      ttl: [""]
    },
    params: "key, value, prefix, ttl(default: 300)",
    doc: "Set Session Cache"
  },
  "session_cache.get": {
    attrs:{
      prefix: [""]
    },
    params: "prefix",
    doc: "Get the Session Cache",
    return: "value from the redis database"
  },
  "session_cache.flush": {
    attrs:{},
    doc: "Flush Session Cache"
  },
  "session_ticket.create": {
    attrs:{
      time: [""],
      nonce: [""],
      otp_value: [""],
      user: [""],
      app: [""]
    },
    params: "time(default: 60), nonce(default: no), otp_value, user, app",
    doc: "Create a ticket",
    return : "Returns ticket"
  },
  "session_ticket.validate": {
    attrs:{
      otp_value: [""]
    },
    params: "otp_value",
    doc: "Validate a ticket",
    return : "If success then return true"
  },
  "session_ticket.set_activity": {
    attrs:{
      app: [""],
      collection: [""],
      module: [""],
      service: [""]
    },
    params: "app, collection, module, service",
    doc: "Set activity of a  ticket"
  },
  "session_ticket.set": {
    attrs:{
      field:[""],
      value:[""]
    },
    params: "field, value(default: error)",
    doc: "Set values of a ticket"
  },
  "session_ticket.get": {
    attrs:{
      field: [""],
  	  otp_value: [""]
    },
    params: "field, otp_value",
    doc: "Get values of a ticket",
    return : "Returns field value from redis database"
  },
  "aw2.subscribe": {
    attrs:{
      api_key: [""],
      phone_no: [""],
      senderid: [""]
    },
    params: "api_key, phone_no, senderid",
    doc: "Subscribe to thrid part newsletter service like mailchimp"
  },
  "template.run": {
    attrs:{
      module: [""]
    },
    params: "module",
    doc: "Run an arbitrary template"
  },
  "template.return": {
    attrs:{},
    doc: "End the active template"
  },
  "templates.add": {
    attrs:{},
    doc: "Add a Template to the Active Module"
  },
  "templates.run": {
    attrs:{},
    doc: "Run a Template of the Active Module"
  },
  "time.start": {
    attrs:{},
    doc: "Start Time. set microtime in Global variables as time_start"
  },
  "time.diff": {
    attrs:{},
    doc: "Difference from start. set microtime in Global variables as time_end and calculate difference",
    return: "Return difference between the time"
  },
  "time.diff_echo": {
    attrs:{},
    doc: "Echo difference from start. set microtime in Global variables as time_end and calculate difference",
    return: "echo out the time difference"
  },
  "util.form_data_array": {
    attrs:{},
    doc: "Collect Form Data and return an array",
    return : "Array of request in key value pair"
  },
  "util.save_csv_page": {
    attrs:{
      pageno: [""],
      rows: [""],
      key: [""],
      ttl: [""]
    },
    params: "pageno, rows, key, ttl",
    doc: "Save CSV page data in redis database"
  },
  "util.async_url": {
    attrs:{
      url: [""]
    },
    params: "url",
    doc: "Run async url",
    return : "success string"
  },
  "util.nonce": {
    attrs:{},
    doc: "creates nonce value for given string"
  },
  "vsession.create": {
    attrs:{
      id: [""]
    },
    params: "id(default: aw2_vsesssion)",
    doc: "Create the Vsession"
  },
  "vsession.exists": {
    attrs:{
      id: [""]
    },
    params: "id(default: aw2_vsesssion)",
    doc: "Check if VSession Exists",
    return : "Returns true if vsession exists otherwise false"
  },
  "vsession.set": {
    attrs:{
      key: [""],
      value: [""],
      prefix: [""],
      ttl: [""],
      id: [""]
    },
    params: "key, value, prefix, ttl(default: 60), id(default: aw2_vsesssion)",
    doc: "set VSession"
  },
  "vsession.get": {
    attrs:{
      id: [""],
      prefix: [""]
    },
    params: "id(default: aw2_vsesssion), prefix",
    doc: "Get VSession.",
    return : "If main is set then returns single key data otherwise all keys data will get return"
  },
  "woo.get": {
    attrs:{},
    doc: "Run WooCommerce actions"
  },
  "wp.signon": {
    attrs:{
      username: [""],
      password: [""]
    },
    params: "username, password",
    doc: "Sign in a User",
    return : "yes on successful signon"
  },
  "zoho.crm": {
    attrs:{},
    doc: "Runs Zoho.com CRM API Actions"
  },
  "template.get": s,
  "template.set": s,
  "module.get": s,
  "module.set": s
};

shortcode_globalAttrs = {
  set: [""],
  "c.ignore": null,
  "c.odd": null,
  "c.even": null,
  "c.eq": null,
  "c.neq": null,
  "c.gt": null,
  "c.lt": null,
  "c.gte": null,
  "c.lte": null,
  "c.true": null,
  "c.false": null,
  "c.yes": null,
  "c.no": null,
  "c.arr": null,
  "c.not_arr": null,
  "c.str": null,
  "c.not_str": null,
  "c.bool": null,
  "c.not_bool": null,
  "c.num": null,
  "c.not_num": null,
  "c.int": null,
  "c.not_int": null,
  "c.date_obj": null,
  "c.not_date_obj": null,
  "c.obj": null,
  "c.not_obj": null,
  "c.zero": null,
  "c.positive": null,
  "c.negative": null,
  "c.ws": null,
  "c.not_ws": null,
  "c.empty": null,
  "c.not_empty": null,
  "c.null": null,
  "c.not_null": null,
  "c.request_exists": null,
  "c.request_not_exists": null,
  "c.contains": null,
  "c.not_contains": null,
  "c.haystack": null,
  "c.exists": null,
  "c.not_exists": null,
  "c.user_can": null,
  "c.user_cannot": null,
  "c.logged_in": null,
  "c.not_logged_in": null,
  "c.device": null,
  "o.exit": null,
  "o.die": null,
  "o.echo": null,
  "o.dump": null,
  "o.console": null,
  "o.log": null,
  "o.destroy": null,
  "o.set": null,
  "o.merge_with": null,
  set: [""],
  dump: ["true"]
};

/*
    used in shortcodemixed.js
    this is the array of shortcodes which is used to change the mode
*/
var shortcodeModeTags = {
    "query.get_results": [
      [null, null, "text/x-mariadb"]
    ],
    "query.get_var": [
      [null, null, "text/x-mariadb"]
    ],
    "query.get_row": [
      [null, null, "text/x-mariadb"]
    ],
    "query.get_col": [
      [null, null, "text/x-mariadb"]
    ],
    "query.query": [
      [null, null, "text/x-mariadb"]
    ],
    "multi.update": [
      [null, null, "text/x-mariadb"]
    ],
    "multi.select": [
      [null, null, "text/x-mariadb"]
    ],
    "sql": [
      [null, null, "text/x-mariadb"]
    ],
    "css.less":  [
      [null, null, "text/x-scss"]
    ],
    "css.style":  [
      [null, null, "text/x-scss"]
    ],
    "query.insert_post":  [
      [null, null, "application/ld+json"]
    ],
    "query.update_post":  [
      [null, null, "application/ld+json"]
    ],
    "query.update_post_meta":  [
      [null, null, "application/ld+json"]
    ],
    "query.delete_post_meta":  [
      [null, null, "application/ld+json"]
    ],
    "query.add_non_unique_post_meta":  [
      [null, null, "application/ld+json"]
    ],
    "query.wp_query":  [
      [null, null, "application/ld+json"]
    ],
    "query.get_comments":  [
      [null, null, "application/ld+json"]
    ],
    "query.update_user_meta":  [
      [null, null, "application/ld+json"]
    ],
    "query.users_builder":  [
      [null, null, "application/ld+json"]
    ],
    "query.posts_builder":  [
      [null, null, "application/ld+json"]
    ],
    "query.query.insert_comment":  [
      [null, null, "application/ld+json"]
    ]
  };
  

/*
    used in shortcode.js
*/
  var shortcodeConfigTags = {
    //No need to close. Can be closed inline. you cant close it on next line.
    autoSelfClosers: {'module.get': true, 'template.get': true,
    'env.get': true,'query.get_post': true,'query.get_post_terms': true,'query.get_post_meta': true
    ,'query.all_post_meta': true,'query.update_post_status': true,'query.delete_post': true,'query.trash_post': true,
    'query.set_post_terms': true,'query.get_posts': true,'query.get_pages': true,'query.get_term_by': true,
    'query.get_term_meta': true, 'query.insert_term': true, 'query.delete_term': true, 'query.get_terms': true,
    'query.get_comment': true, 'query.get_user_by': true, 'query.get_user_meta': true, 
    'query.get_users': true, 'query.delete_revisions': true, 'query.term_exists': true
  },
    //No need to close. Can be closed inline. Can be close on next line.
    implicitlyClosed: {'query.set_post':true, 'aw2.get': true, 'aw2.set': true, 'module.set': true, 'template.set': true,
    'env.set': true, 'query.get_results': true, 'query.get_var': true, 'query.get_row': true, 'query.get_col': true,
    'query.query': true, 'query.insert_post': true,
    'query.update_post': true, 'query.update_post_meta': true, 'query.delete_post_meta': true,
    'query.add_non_unique_post_meta': true, 'query.wp_query': true, 'query.get_comments': true,
    'query.update_user_meta': true, 'query.users_builder': true, 'query.posts_builder': true, 'query.insert_comment': true
   },
    contextGrabbers: {
      'if.equal': {'if.equal': true},
      'if.not_equal': {'if.not_equal': true},
      'if.greater_equal': {'if.greater_equal': true},
      'if.greater_than': {'if.greater_than': true},
      'if.less_equal': {'if.less_equal': true},
      'if.less_than': {'if.less_than': true},
      'if.whitespace': {'if.whitespace': true},
      'if.not_whitespace': {'if.not_whitespace': true},
      'if.false': {'if.false': true},
      'if.true': {'if.true': true},
      'if.yes': {'if.yes': true},
      'if.no': {'if.no': true},
      'if.not_empty': {'if.not_empty': true},
      'if.empty': {'if.empty': true},
      'if.odd': {'if.odd': true},
      'if.even': {'if.even': true},
      'if.arr': {'if.arr': true},
      'if.not_arr': {'if.not_arr': true},
      'if.str': {'if.str': true},
      'if.not_str': {'if.not_str': true},
      'if.bool': {'if.bool': true},
      'if.not_bool': {'if.not_bool': true},
      'if.greater_than_zero': {'if.greater_than_zero': true},
      'if.num': {'if.num': true},
      'if.not_num': {'if.not_num': true},
      'if.int': {'if.int': true},
      'if.not_int': {'if.not_int': true},
      'if.date_obj': {'if.date_obj': true},
      'if.not_date_obj': {'if.not_date_obj': true},
      'if.obj': {'if.obj': true},
      'if.not_obj': {'if.not_obj': true},
      'if.user_can': {'if.user_can': true},
      'if.user_cannot': {'if.user_cannot': true},
      'if.logged_in': {'if.logged_in': true},
      'if.not_logged_in': {'if.not_logged_in': true},
      'if.request': {'if.request': true},
      'if.not_request': {'if.not_request': true},
      'if.device': {'if.device': true},
      'if.contains': {'if.contains': true},
      'if.not_contains': {'if.not_contains': true},
    },
    doNotIndent: {"pre": true},
    allowUnquoted: true,
    allowMissing: true,
    caseFold: true
  }