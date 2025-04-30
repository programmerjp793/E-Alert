package com.example.ealert.app

import android.os.Bundle
import android.view.View
import android.widget.ArrayAdapter
import android.widget.Toast
import androidx.appcompat.app.AppCompatActivity
import androidx.recyclerview.widget.LinearLayoutManager
import androidx.recyclerview.widget.RecyclerView
import com.example.ealert.R
import com.example.ealert.adapter.ReportHistoryAdapter
import com.example.ealert.app.response.ReportHistory
import com.example.ealert.app.retrofit.RetrofitClient
import com.example.ealert.app.response.ReportHistoryResponse
import com.google.android.material.textfield.MaterialAutoCompleteTextView
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext
import retrofit2.Response

class ReportHistoryActivity : AppCompatActivity() {

    private lateinit var recyclerView: RecyclerView
    private lateinit var adapter: ReportHistoryAdapter
    private lateinit var progressBar: View
    private lateinit var statusFilterDropdown: MaterialAutoCompleteTextView
    private var allReports = mutableListOf<ReportHistory>()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_report_history)

        // Initialize views
        recyclerView = findViewById(R.id.reportHistoryRecyclerView)
        progressBar = findViewById(R.id.progressBar)
        statusFilterDropdown = findViewById(R.id.statusFilterDropdown)

        // Setup RecyclerView
        recyclerView.layoutManager = LinearLayoutManager(this)
        adapter = ReportHistoryAdapter()
        recyclerView.adapter = adapter

        // Setup status filter dropdown
        setupStatusFilter()

        // Load data
        loadReportHistory()
    }

    private fun setupStatusFilter() {
        val statusOptions = listOf("All", "Pending", "In-progress", "Resolved", "Cancelled")
        val adapter = ArrayAdapter(this, android.R.layout.simple_dropdown_item_1line, statusOptions)
        statusFilterDropdown.setAdapter(adapter)

        statusFilterDropdown.setOnItemClickListener { _, _, position, _ ->
            val selectedStatus = if (position == 0) null else statusOptions[position]
            filterReports(selectedStatus)
        }
    }

    private fun filterReports(status: String?) {
        val filteredList = if (status.isNullOrEmpty() || status == "All") {
            allReports
        } else {
            allReports.filter { it.status.equals(status, ignoreCase = true) }
        }
        adapter.submitList(filteredList)
    }

    private fun loadReportHistory() {
        progressBar.visibility = View.VISIBLE
        val sharedPref = getSharedPreferences("user_prefs", MODE_PRIVATE)
        val userId = sharedPref.getInt("user_id", 0)

        if (userId == 0) {
            progressBar.visibility = View.GONE
            Toast.makeText(this, "User not logged in", Toast.LENGTH_SHORT).show()
            return
        }

        CoroutineScope(Dispatchers.IO).launch {
            try {
                val response = RetrofitClient.instance.getReportHistory(userId)
                withContext(Dispatchers.Main) {
                    progressBar.visibility = View.GONE
                    handleResponse(response)
                }
            } catch (e: Exception) {
                withContext(Dispatchers.Main) {
                    progressBar.visibility = View.GONE
                    Toast.makeText(
                        this@ReportHistoryActivity,
                        "Network error: ${e.message}",
                        Toast.LENGTH_SHORT
                    ).show()
                }
            }
        }
    }

    private fun handleResponse(response: Response<ReportHistoryResponse>) {
        if (response.isSuccessful) {
            response.body()?.let { responseBody ->
                if (responseBody.status == "success") {
                    allReports = responseBody.reports.toMutableList()
                    adapter.submitList(allReports)
                } else {
                    Toast.makeText(
                        this,
                        responseBody.message,
                        Toast.LENGTH_SHORT
                    ).show()
                }
            }
        } else {
            Toast.makeText(
                this,
                "Error: ${response.message()}",
                Toast.LENGTH_SHORT
            ).show()
        }
    }
}